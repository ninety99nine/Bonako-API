<?php

namespace App\Repositories;

use App\Models\User;
use App\Traits\Base\BaseTrait;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AiAssistantRepository extends BaseRepository
{
    use BaseTrait;

    /**
     *  Create an Ai Assistant record for the user
     *
     *  @param User $user
     *  @return AiAssistantRepository
     */
    public function createAiAssistant(User $user)
    {
        return parent::create([
            'user_id' => $user->id
        ]);
    }

    /**
     *  Show the Ai Assistant record for the user
     *
     *  @param User $user
     *  @return AiAssistantRepository
     *  @throws ModelNotFoundException
     */
    public function showAiAssistant(User $user)
    {
        $aiAssistant = $user->aiAssistant;

        if(is_null($aiAssistant)) {
            return $this->createAiAssistant($user);
        }else{
            return $this->setModel($user->aiAssistant);
        }
    }
}
