<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\AiAssistant;
use Illuminate\Http\Request;
use App\Traits\Base\BaseTrait;
use App\Models\AiMessageCategory;
use OpenAI\Laravel\Facades\OpenAI;
use App\Repositories\BaseRepository;
use Rajentrivedi\TokenizerX\TokenizerX;
use App\Repositories\AiAssistantRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\AiMessageLimitReachedException;

class AiMessageRepository extends BaseRepository
{
    use BaseTrait;

    private $requestTokensUsed = 0;

    /**
     *  Deleting ai messages does not require confirmation.
     */
    protected $requiresConfirmationBeforeDelete = false;

    /**
     *  Return the aiAssistantRepository instance
     *
     *  @return AiAssistantRepository
     */
    public function aiAssistantRepository()
    {
        return resolve(AiAssistantRepository::class);
    }

    /**
     *  Return the SubscriptionRepository instance
     *
     *  @return SubscriptionRepository
     */
    public function subscriptionRepository()
    {
        return resolve(SubscriptionRepository::class);
    }

    /**
     *  Create a new user and ai messages
     *
     *  @param User $user
     *  @param Request $request
     *  @return AiMessageRepository|null
     */
    public function createUserAiMessage(User $user, Request $request)
    {
        //  Get datetime of the request
        $requestAt = now();

        //  Set the stream
        $stream = $request->input('stream');

        //  Set the category id
        $categoryId = $request->input('category_id');

        //  Set the user content
        $userContent = $request->input('user_content');

        //  Get the AI Assistant information for the user
        $aiAssistant = $this->aiAssistantRepository()->showAiAssistant($user)->model;

        $doesNotHaveAnyFreeRequestsLeft = $aiAssistant->total_requests >= AiAssistant::MAXIMUM_FREE_REQUESTS;
        $doesNotHaveAnyTokensLeft = $aiAssistant->remaining_paid_tokens <= 0;

        //  If we don't have any free requests left and we don't have any tokens left
        if( $doesNotHaveAnyFreeRequestsLeft && $doesNotHaveAnyTokensLeft) {

            $assistantContent = 'Hi '.auth()->user()->first_name.', please subscribe to ask me more questions';

            //  Indicate that a subscription is required
            if($aiAssistant->requires_subscription == false) {
                $aiAssistant->update(['requires_subscription' => true]);
            }

            if($stream) {

                echo $assistantContent;
                echo '<END_STREAMING_SSE>';
                ob_flush();
                flush();

            }else{

                throw new AiMessageLimitReachedException($assistantContent);

            }

        }else{

            //  Indicate that a subscription is not required
            if($aiAssistant->requires_subscription == true) {
                $aiAssistant->update(['requires_subscription' => false]);
            }

            //  Get the previous AI Messages
            $previousAiMessages = $user->aiMessages()->latest()->take(10)->get();

            //  Get the AI Message Category (From Cache or Database)
            $aiMessageCategory = AiMessageCategory::find($categoryId);

            //  Send the reply to the Ai Model and return the AI message reply content
            $result = $this->sendUserContentToAiAssistant($userContent, $previousAiMessages, $aiMessageCategory, $stream);

            //  Get datetime of the response
            $responseAt = now();

            //  If the result is an array, then this is a non-stream response (The entire reply is together as one big chunk)
            if(is_array($result)) {

                //  Extract the assistant content from the message of the first choice
                $assistantContent = $result['choices'][0]['message']['content'];

                //  Extract the usage tokens
                $responseTokensUsed = $result['usage']['completion_tokens'];
                $requestTokensUsed = $result['usage']['prompt_tokens'];
                $totalTokensUsed = $result['usage']['total_tokens'];

                $remainingPaidTokens = $doesNotHaveAnyFreeRequestsLeft ? ($aiAssistant->remaining_paid_tokens - $totalTokensUsed) : $aiAssistant->remaining_paid_tokens;
                $freeTokensUsed = $doesNotHaveAnyFreeRequestsLeft ? $aiAssistant->free_tokens_used : ($aiAssistant->free_tokens_used + $totalTokensUsed);
                $requiresSubscription = $remainingPaidTokens <= 0;

                $aiAssistant->update([
                    'response_tokens_used' => $aiAssistant->response_tokens_used + $responseTokensUsed,
                    'request_tokens_used' => $aiAssistant->request_tokens_used + $requestTokensUsed,
                    'total_tokens_used' => $aiAssistant->total_tokens_used + $totalTokensUsed,
                    'total_requests' => $aiAssistant->total_requests + 1,
                    'requires_subscription' => $requiresSubscription,
                    'remaining_paid_tokens' => $remainingPaidTokens,
                    'free_tokens_used' => $freeTokensUsed,
                ]);

                //  Create the AI Message and return this AiMessageRepository
                return $this->create([
                    'remaining_paid_tokens' => $remainingPaidTokens,
                    'response_tokens_used' => $responseTokensUsed,
                    'request_tokens_used' => $requestTokensUsed,
                    'assistant_content' => $assistantContent,
                    'total_tokens_used' => $totalTokensUsed,
                    'free_tokens_used' => $freeTokensUsed,
                    'user_content' => $userContent,
                    'category_id' => $categoryId,
                    'response_at' => $responseAt,
                    'request_at' => $requestAt,
                    'user_id' => $user->id,
                ]);

            //  If the result is not an array, then this is a stream response (The reply was separated as multiple small chunks joined together to make on big chunk)
            }else{

                //  The result as is, is the assistant content
                $assistantContent = $result;

                //  Calculate the usage tokens
                $requestTokensUsed = $this->requestTokensUsed;
                $responseTokensUsed = TokenizerX::count($assistantContent);
                $totalTokensUsed = $requestTokensUsed + $responseTokensUsed;

                $remainingPaidTokens = $doesNotHaveAnyFreeRequestsLeft ? ($aiAssistant->remaining_paid_tokens - $totalTokensUsed) : $aiAssistant->remaining_paid_tokens;
                $freeTokensUsed = $doesNotHaveAnyFreeRequestsLeft ? $aiAssistant->free_tokens_used : ($aiAssistant->free_tokens_used + $totalTokensUsed);
                $requiresSubscription = $remainingPaidTokens <= 0;

                $aiAssistant->update([
                    'response_tokens_used' => $aiAssistant->response_tokens_used + $responseTokensUsed,
                    'request_tokens_used' => $aiAssistant->request_tokens_used + $requestTokensUsed,
                    'total_tokens_used' => $aiAssistant->total_tokens_used + $totalTokensUsed,
                    'total_requests' => $aiAssistant->total_requests + 1,
                    'requires_subscription' => $requiresSubscription,
                    'remaining_paid_tokens' => $remainingPaidTokens,
                    'free_tokens_used' => $freeTokensUsed,
                ]);

                /**
                 *  Create the AI Message but don't return anything since the response has been returning small chunks of
                 *  the stream using PHP "echo" statement as seen within the handleStreamRequest() method. Attempting to
                 *  return this repository or the created AI Message will throw an exception e.g:
                 *
                 *  Something went wrong","error":"Symfony\\Component\\HttpFoundation\\Response::setContent(): Argument #1 ($content) must be of type ?string, App\\Repositories\\AiMessageRepository given
                 *
                 *  This is because the response is no longer a regular response that can return any kind of data, but is now a stream response that can only return string values.
                 */
                $this->create([
                    'remaining_paid_tokens' => $remainingPaidTokens,
                    'response_tokens_used' => $responseTokensUsed,
                    'request_tokens_used' => $requestTokensUsed,
                    'assistant_content' => $assistantContent,
                    'total_tokens_used' => $totalTokensUsed,
                    'free_tokens_used' => $freeTokensUsed,
                    'user_content' => $userContent,
                    'category_id' => $categoryId,
                    'response_at' => $responseAt,
                    'request_at' => $requestAt,
                    'user_id' => $user->id,
                ]);

            }

        }
    }

    /**
     *  Send the user content to the AI Assistant so that we can return a reply
     *
     *  @param string $content - The message content provided by the user
     *  @param Collection $previousAiMessages - The previous AI Messages created by the user
     *  @param AiMessageCategory $aiMessageCategory - The AI Message Category
     *  @param boolean $stream - True/False whether we want to allow streaming
     *  @return string|null
     */
    private function sendUserContentToAiAssistant($content, $previousAiMessages, $aiMessageCategory, $stream = false)
    {
        $data = $this->prepareRequestPayload($content, $previousAiMessages, $aiMessageCategory, $stream);

        if($stream) {

            return $this->handleStreamRequest($data);

        }else{

            return $this->handleNonStreamRequest($data);

        }

    }

    /**
     *  Prepare the OpenAI request payload
     *
     *  @param string $content - The message content provided by the user
     *  @param Collection $previousAiMessages - The previous AI Messages created by the user
     *  @param AiMessageCategory $aiMessageCategory - The AI Message Category
     *  @param boolean $stream - True/False whether we want to allow streaming
     *  @return array
     */
    private function prepareRequestPayload($content, $previousAiMessages, $aiMessageCategory, $stream)
    {
        $model = config('app.OPENAI_API_MODEL');
        $maxTokens = config('app.OPENAI_API_MAX_TOKENS');
        $temperature = config('app.OPENAI_API_TEMPERATURE');

        /**
         *  @var User $user
         */
        $user = auth()->user();

        $systemContent = $aiMessageCategory->system_prompt.'. ';
        $systemContent .= 'You are talking to '.$user->first_name.' who is an entrepreneur. ';
        $systemContent .= 'Here\'s more information about '.$user->first_name.":\n";
        $systemContent .= 'First name: '.$user->first_name."\n";
        $systemContent .= 'Last name: '.$user->last_name."\n";
        $systemContent .= 'Mobile number: '.$user->mobile_number->withoutExtension."\n";

        $storeNames = $user->stores()->joinedTeamAsCreator()->pluck('name');
        $totalStores = count($storeNames);

        if( $totalStores ) {

            $storeNamesInSentence = collect($storeNames)->join(', ', ', and ');

            $systemContent .= $user->first_name.' is the owner of '.$totalStores.' '.($totalStores == 1? 'business' : 'businesses').' called '.$storeNamesInSentence.'. ';

        }

        $systemContent .= 'Make sure that you personalize your replies e.g When '.$user->first_name.' greets you, you can reply "Hi '.$user->first_name.' how can I help you today?", however don\'t repeat the person\'s name unnecessarily. ';

        if($stream == false) {
            $systemContent .= 'Reply as quick as possible since this chat is served on a USSD interface which timeouts in a few seconds.';
        }

        $messages = [];

        //  Add the system content (Give general context)
        array_push($messages, [
            'role' => 'system',
            'content' => $systemContent
        ]);

        /**
         *  Add previous user and assistant messages (Give historical context)
         *  Use the reverse() method of the Laravel Collections so that we can
         *  order the messages from the oldest to the latest.
         */
        foreach($previousAiMessages->reverse() as $previousAiMessage) {

            /**
             *  Note that we need to limit the amount of content that we collect from the previous user and assistant
             *  replies so that it's not too expensive when making a request since too much content would increase
             *  the total number of request tokens and therefore make the requests too expensive.
             */
            $userContent = $previousAiMessage->user_content;
            $assistantContent = $previousAiMessage->assistant_content;

            if (strlen($userContent) > 200) {
                $userContent = substr($userContent, 0, 200);
            }

            if (strlen($assistantContent) > 200) {
                $assistantContent = substr($assistantContent, 0, 200);
            }

            array_push($messages, [
                'role' => 'user',
                'content' => $userContent
            ]);

            array_push($messages, [
                'role' => 'assistant',
                'content' => $assistantContent
            ]);

        }

        //  Add the current user message
        array_push($messages, [
            'role' => 'user',
            'content' => $content
        ]);

        foreach($messages as $message) {

            $this->requestTokensUsed += TokenizerX::count($message['content']);

        }

        return [
            "model" => $model,
            "stream" => $stream,    // This tells OpenAi that we want to allow streaming
            'messages' => $messages,
            "max_tokens" => (int) $maxTokens,
            "temperature" => (float) $temperature,
        ];
    }

    /**
     *  Handle a stream request
     *
     *  @param array $data
     *  @return string
     */
    private function handleStreamRequest($data)
    {
        /**
         *  Refer to the following tutorial to lean more about openai streaming response.
         *
         *  Reference: https://ahmadrosid.com/blog/laravel-openai-streaming-response
         */
        $stream = OpenAI::chat()->createStreamed($data);

        $assistantContent = '';

        foreach ($stream as $response) {

            /**
             *  Example of the response structure:
             *
             *  $response = {
             *      "id":"chatcmpl-8Aa01R9e5fwg4WsUAJnxLv4NU26TV",
             *      "object":"chat.completion.chunk",
             *      "created":1697532353,
             *      "model":"gpt-3.5-turbo-16k-0613",
             *      "choices":[
             *          {
             *              "index":0,
             *              "delta":{
             *                  "role":"assistant",
             *                  "content":"","functionCall":null
             *              },
             *              "finishReason":null
             *          }
             *      ]
             *  }
             */
            $text = $response->choices[0]->delta->content;

            $assistantContent .= $text;

            if (connection_aborted()) {
                break;
            }

            echo $text;
            ob_flush();
            flush();

        }

        echo '<END_STREAMING_SSE>';
        ob_flush();
        flush();

        return $assistantContent;
    }

    /**
     *  Handle a non stream request
     *
     *  @param array $data
     *  @return array
     */
    private function handleNonStreamRequest($data)
    {
        /**
         *  https://platform.openai.com/docs/api-reference/chat/create
         *
         *  $result = [
         *      "id" => "chatcmpl-8CVOepuxHBCIUX72vZcFbmTn4F39Z"
         *      "object" => "chat.completion"
         *      "created" => 1697991316
         *      "model" => "gpt-3.5-turbo-16k-0613"
         *      "choices" => [
         *          [
         *              "index" => 0
         *              "message" => [
         *                  "role" => "assistant"
         *                  "content" => "You can purchase goats from various sources such as livestock markets, local farmers, or specialized livestock auctions. Additionally, you can also explore online platforms and classifieds that connect buyers and sellers of livestock. It's recommended to visit and evaluate the goats in person before making a purchase."
         *              ]
         *              "finish_reason" => "stop"
         *          ]
         *      ]
         *      "usage" => array:3 [
         *          "prompt_tokens" => 232
         *          "completion_tokens" => 55
         *          "total_tokens" => 287
         *      ]
         *  ]
         */
        $result = OpenAI::chat()->create($data)->toArray();

        return $result;
    }
}
