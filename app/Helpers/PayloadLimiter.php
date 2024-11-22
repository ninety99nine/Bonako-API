<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PayloadLimiter
{
    private $payload;
    private $limiters;

    /**
     * @param array $payload - The data payload to be limited based on the supplied limiter
     * @param string $limiter - The string to limit the payload e.g first_name, user.address.country
     */
    public function __construct(array $payload, string $limiter)
    {
        $this->payload = $payload;
        $this->limiters = $this->extractLimiters($limiter);
    }

    /**
     *  Extract limiters
     *
     * @param string $limiter - The string to limit the payload
     */
    public function extractLimiters(string $limiter): array
    {
        $limiter = $this->normalizeLimiter($limiter);
        $limiter = $this->convertFromShortToLongForm($limiter);

        return explode(',', $limiter);
    }

    /**
     *  Normalize limiter
     *
     * @param string $limiter - The string to limit the payload
     */
    public function normalizeLimiter(string $limiter): string
    {
        // Replace consecutive spaces with nothing  e.g "first_name , last_name , address" into "first_name,last_name,address"
        $limiter = preg_replace('/\s+/', '', $limiter);

        // Replace consecutive commas with just one comma e.g "first_name,,last_name,,,address" into "first_name,last_name,address"
        $limiter = preg_replace('/,+/', ',', $limiter);

        // Replace consecutive periods with just one period e.g "address..home...city" into "address.home.city"
        $limiter = preg_replace('/\.{2,}/', '.', $limiter);

        // Replace consecutive plus symbols with just one plus symbol e.g "address.home.city|||plot" into "address.home.city|plot"
        $limiter = preg_replace('/\|{2,}/', '|', $limiter);

        // Define the regex pattern to match leading or trailing non-alphanumeric characters
        $pattern = '/^[^a-zA-Z0-9]+|[^a-zA-Z0-9]+$/';

        // Remove non-alphanumeric characters from the beginning and end of the string using regex
        $limiter = preg_replace($pattern, '', $limiter);

        // Convert to snakecase format
        $limiter = Str::snake($limiter);

        //  Return the limiters
        return $limiter;
    }

    /**
     * Convert from short form to long form
     *
     * From: $limiter = "user.first_name|last_name"
     *
     * To: $limiter = "user.first_name,user.last_name"
     *
     * @param string $limiter - The string to limit the payload
     */
    public function convertFromShortToLongForm(string $limiter): string
    {
        $limiters = explode(',', $limiter);
        $formattedLimiters = [];

        foreach ($limiters as $currLimiter) {

            // If the current limiter contains a '|', we need to convert it
            if (Str::contains($currLimiter, '|')) {

                // Get the root part before the '|'
                $root = Str::beforeLast($currLimiter, '.');

                // Get the part after the last dot
                $afterRoot = Str::afterLast($currLimiter, '.');

                // Split the part after the dot by '|'
                $shortHandLimiters = explode('|', $afterRoot);

                // Build the full limiters using the root and the shorthand parts
                foreach ($shortHandLimiters as $shortHandLimiter) {
                    $formattedLimiters[] = $root . '.' . $shortHandLimiter;
                }

            } else {

                // If no shorthand, just add the current limiter as is
                $formattedLimiters[] = $currLimiter;

            }
        }

        // Return the result as a comma-separated string
        return implode(',', $formattedLimiters);
    }

    /**
     *  ------------------------------
     *  How getLimitedPayload() Works:
     *  ------------------------------
     *
     *  $this->payload = [
     *      "firstName" => "John",
     *      "lastName" => "Doe",
     *      "address" => [
     *          "home" => [
     *              "city" => "Gaborone",
     *              "plot" => "Plot 123",
     *              "street" => "ABC"
     *          ],
     *          "work" => [
     *              "city" => "Gaborone",
     *              "plot" => "Plot 456",
     *              "street" => "DEF"
     *          ]
     *      ],
     *  ];
     *
     *  The limiter is provided as the second parameter e.g
     *
     *  (1) $limiter = 'first_name';
     *
     *  returns [
     *      "firstName" => "John"
     *  ];
     *
     *  (2) $limiter = 'first_name, last_name';
     *
     *  returns [
     *      "firstName" => "John",
     *      "lastName" => "Doe"
     *  ];
     *
     *  (3) $limiter = 'first_name, last_name, address';
     *
     *  returns [
     *      "firstName" => "John",
     *      "lastName" => "Doe",
     *      "address" => [
     *          "home" => [
     *              "city" => "Gaborone",
     *              "plot" => "Plot 123",
     *              "street" => "ABC"
     *          ],
     *          "work" => [
     *              "city" => "Gaborone",
     *              "plot" => "Plot 456",
     *              "street" => "DEF"
     *          ]
     *      ],
     *  ];
     *
     *  (4) $limiter = 'first_name, last_name, address.home';
     *
     *  returns [
     *      "firstName" => "John",
     *      "lastName" => "Doe",
     *      "address" => [
     *          "home" => [
     *              "city" => "Gaborone",
     *              "plot" => "Plot 123",
     *              "street" => "ABC"
     *          ]
     *      ],
     *  ];
     *
     *  (5) $limiter = 'first_name, last_name, address.home.city';
     *
     *  returns [
     *      "firstName" => "John",
     *      "lastName" => "Doe",
     *      "address" => [
     *          "home" => [
     *              "city" => "Gaborone"
     *          ]
     *      ],
     *  ];
     *
     *  (6) $limiter = 'first_name, last_name, address.home.city|plot';
     *
     *  returns [
     *      "firstName" => "John",
     *      "lastName" => "Doe",
     *      "address" => [
     *          "home" => [
     *              "city" => "Gaborone",
     *              "plot" => "Plot 123",
     *          ]
     *      ],
     *  ];
     *
     *  (7) $limiter = 'first_name, last_name, address.home.city|plot, address.work.city|plot';
     *
     *  returns [
     *      "firstName" => "John",
     *      "lastName" => "Doe",
     *      "address" => [
     *          "home" => [
     *              "city" => "Gaborone",
     *              "plot" => "Plot 123",
     *          ],
     *          "work" => [
     *              "city" => "Gaborone",
     *              "plot" => "Plot 456"
     *          ]
     *      ],
     *  ];
     *
     *  ------------------------------------------------------
     *  What about multiple similar entries e.g List of users?
     *  ------------------------------------------------------
     *
     *  Assume the data payload is as follows:
     *
     *  $payload = [
     *      "data" => [
     *          [
     *              "firstName" => "John",
     *              "lastName" => "Doe",
     *              "age" => 15
     *          ],
     *          [
     *              "firstName" => "Jane",
     *              "lastName" => "Moe",
     *              "age" => 15
     *          ]
     *      ]
     *  ];
     *
     *  (1) $limiter = 'data';
     *
     *  returns [
     *      "data" => [
     *          [
     *              "firstName" => "John",
     *              "lastName" => "Doe",
     *              "age" => 15
     *          ],
     *          [
     *              "firstName" => "Jane",
     *              "lastName" => "Moe",
     *              "age" => 15
     *          ]
     *      ]
     *  ];
     *
     *  (2) $limiter = 'data.firstName';
     *
     *  returns [
     *      "data" => [
     *          [
     *              "firstName" => "John"
     *          ],
     *          [
     *              "firstName" => "Jane"
     *          ]
     *      ]
     *  ];
     *
     *  (3) $limiter = 'data.firstName:lastName';
     *
     *  returns [
     *      "data" => [
     *          [
     *              "firstName" => "John",
     *              "lastName" => "Doe"
     *          ],
     *          [
     *              "firstName" => "Jane",
     *              "lastName" => "Moe"
     *          ]
     *      ]
     *  ];
     *
     *  (4) $limiter = 'data.firstName:lastName:age';
     *
     *  returns [
     *      "data" => [
     *          [
     *              "firstName" => "John",
     *              "lastName" => "Doe",
     *              "age" => 15
     *          ],
     *          [
     *              "firstName" => "Jane",
     *              "lastName" => "Moe",
     *              "age" => 15
     *          ]
     *      ]
     *  ];
     *
     */
    public function getLimitedPayload(): array
    {
        // Flatten the original payload
        $payloadInDotNotation = $this->flattenPayload($this->payload);

        // Filter the keys by checking exact matches or matching the pattern
        $limitedPayloadInDotNotation = collect($payloadInDotNotation)
            ->filter(function ($value, $dotNotation) {

                /**
                 *  To support limiting on a list of arrays:
                 *
                 *  Replace $key = "0.name" or "data.0.name" (Where 0 can be any other index number of a list)
                 *  With $key = "name" or "data.name"        (Index numbers are removed)
                 */
                $dotNotation = preg_replace('/\d+\./', '', $dotNotation);

                //  Convert to snake case format
                $dotNotation = Str::snake($dotNotation);

                return in_array($dotNotation, $this->limiters) || collect($this->limiters)->contains(function($limiter) use ($dotNotation) {
                    return Str::startsWith($dotNotation, $limiter.'.');
                });

            })->all();

        return Arr::undot($limitedPayloadInDotNotation);
    }

    /**
     * Flatten the payload, accounting for both arrays and objects at all levels.
     *
     * @param mixed $payload
     * @param string $prefix - Used for recursion to build the dot notation
     * @return array
     */
    public function flattenPayload($payload, $prefix = '')
    {
        $flattened = [];
        $stack = new \SplStack();
        $stack->push([$payload, $prefix]);

        while (!$stack->isEmpty()) {

            // Pop the current level of the stack
            list($current, $currentPrefix) = $stack->pop();

            // If the current value is an array or object, iterate over it
            if (is_array($current) || is_object($current)) {

                // Check this is an empty array is empty
                if (empty($current)) {

                    //  Add this empty array to the flattened result
                    $flattened[$currentPrefix] = [];
                    continue;

                }

                foreach ($current as $key => $value) {

                    // Create a new key based on the prefix
                    $newKey = $currentPrefix != '' ? $currentPrefix . '.' . $key : $key;

                    // Push the next level onto the stack
                    if (is_array($value) || is_object($value)) {

                        $stack->push([$value, $newKey]);  // Continue processing deeper levels

                    } else {

                        // If the value is not an array or object, add it to the result
                        $flattened[$newKey] = $value;

                    }

                }

            } else {

                // If the current value is not an array or object, just add it to the result
                $flattened[$currentPrefix] = $current;

            }
        }

        return $flattened;
    }

}
