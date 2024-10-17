<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class PayloadLimiter
{
    private $payload;
    private $limiter;
    private $isCamelCaseFormat;
    private $payloadNamingConvention;

    /**
     *  -----------------------------
     *  How to use the PayloadLimiter
     *  -----------------------------
     *
     *  (new PayloadLimiter($payload, $limiter))->getPayload();
     *
     *  @var array $payload - The data payload to be limited based on the supplied limiter
     *  @var string $limiter - The string to limit the payload e.g first_name, last_name, _links.self.href
     */
    public function __construct(array $payload, string $limiter, $isCamelCaseFormat = null)
    {
        $this->payload = $payload;
        $this->limiter = $limiter;

        $this->payloadNamingConvention = (new PayloadNamingConvention());
        $this->isCamelCaseFormat = $isCamelCaseFormat ?? $this->payloadNamingConvention->isCamelCaseFormat();
    }

    /**
     *  Get the limited payload
     */
    public function getLimitedPayload(): array
    {
        $this->limiter = $this->prepareLimiter($this->limiter);
        return $this->limitPayload($this->payload, $this->limiter);
    }

    /**
     *  Prepare the limiter
     */
    public function prepareLimiter(string $limiter): string
    {
        /**
         *  Capture the filters:
         *
         *  $limiter = "firstName, lastName, address.home.city+plot"
         *
         *  Into:
         *
         *  $filters = [
         *      "first_name",
         *      "last_name",
         *      "address.home.city+plot"
         *  ];
         */

        // Replace consecutive spaces with nothing
        $limiter = preg_replace('/\s+/', '', $limiter);

        // Replace consecutive commas with just one comma e.g "first_name,,last_name,,,address" into "first_name,last_name,address"
        $limiter = preg_replace('/,+/', ',', $limiter);

        // Replace consecutive periods with just one period e.g "address..home...city" into "address.home.city"
        $limiter = preg_replace('/\.+/', '.', $limiter);

        // Replace consecutive plus symbols with just one plus symbol e.g "address.home.city+++plot" into "address.home.city+plot"
        $limiter = preg_replace('/\++/', '+', $limiter);

        // Replace consecutive plus symbols with just one plus symbol e.g "address.home.city+++plot" into "address.home.city+plot"
        $limiter = preg_replace('/\++/', '+', $limiter);

        // Escape special characters for safe use in regex
        $escapedChars = preg_quote(',.+', '/');

        /**
         *  Define the regex pattern to match leading or trailing occurrences of the specified characters.
         *
         *  This means that we will remove any trailing characters starting or ending with "," or "." or "+"
         *
         *  e.g:
         *
         *  From: ..,,firstName,lastName,address.home.city..++
         *  To:   firstName,lastName,address.home.city
         *
         *  Notice that the trailing characters are removed from the start and end of the filter string
         */
        $pattern = '/^[' . $escapedChars . ']+|[' . $escapedChars . ']+$/';

        // Remove specified characters from the beginning and end of the string using regex
        $limiter = preg_replace($pattern, '', $limiter);

        foreach([",", ".", "+"] as $trailingCharacter) {

            // Remove specified characters from the beginning and end of the string e.g "...first_name,last_name,,," into "first_name,last_name"
            $limiter = trim($limiter, $trailingCharacter);

        }

        return $limiter;
    }

    /**
     *  -------------------------
     *  How limitPayload() Works:
     *  -------------------------
     *
     *  The data payload that must be limited is provided as the first parameter e.g
     *
     *  $payload = [
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
     *  (6) $limiter = 'first_name, last_name, address.home.city+plot';
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
     *  (6) $limiter = 'first_name, last_name, address.home.city+plot, address.work.city+plot';
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
     *  (2) $limiter = 'data.*';
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
     *  (3) $limiter = 'data.*.firstName';
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
     *  (4) $limiter = 'data.*.firstName:lastName';
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
     *  (5) $limiter = 'data.*.firstName:lastName:age';
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
    public function limitPayload(array $payload, string $limiter): array
    {
        if(!empty($limiter)) {

            $filters = Str::of($limiter)->explode(',')->map(function($field1) {

                /**
                 *  Since a $field1 could be equal to "user.firstName+_links". Running the following:
                 *
                 *  $payloadNamingConvention->convertKeyToCamelCaseFormat("user.firstName+_links");
                 *
                 *  This would return "user.firstName+Links" which is not ideal.
                 *
                 *  We need to continue to explode the string further e.g ['user', 'firstName+_links']
                 */
                return Str::of($field1)->explode('.')->map(function($field2) {

                    /**
                     *  Since a $field2 could be equal to "firstName+_links". Running the following:
                     *
                     *  $payloadNamingConvention->convertKeyToCamelCaseFormat("firstName+_links");
                     *
                     *  This would return "firstName+Links" which is not ideal.
                     *
                     *  We need to continue to explode the string further e.g ['firstName', '_links']
                     *  so that we can now call the following:
                     *
                     *  $payloadNamingConvention->convertKeyToCamelCaseFormat("firstName");
                     *  $payloadNamingConvention->convertKeyToCamelCaseFormat("_links");
                     *
                     *  And then join the resulting outputs e.g Join ['firstName', '_links'] into "firstName+_links"
                     */
                    return Str::of($field2)->explode('+')->map(function($field3) {

                        if($this->isCamelCaseFormat) {

                            //  Convert the key to camelcase e.g account_exists to accountExists
                            return $this->payloadNamingConvention->convertKeyToCamelCaseFormat($field3);

                        }else{

                            //  Convert the key to snakecase e.g accountExists to account_exists
                            return $this->payloadNamingConvention->convertKeyToSnakeCaseFormat($field3);

                        }

                    })->join('+');

                })->join('.');

            })->toArray();

            $result = [];

            foreach ($filters as $filter) {

                /**
                 *  Capture the filters:
                 *
                 *  $filter = "first_name" into:
                 *
                 *  $nestedKeys = ["first_name"]
                 *
                 *  or:
                 *
                 *  $filter = "last_name" into:
                 *
                 *  $nestedKeys = ["last_name"]
                 *
                 *  or:
                 *
                 *  $filter = "address.home.city+plot" into:
                 *
                 *  $nestedKeys = ["address", "home", "city+plot"]
                 */
                $nestedKeys = explode('.', $filter);

                $temp = $payload;

                /**
                 *  For $nestedKeys = ["first_name"]
                 *  For $nestedKeys = ["last_name"]
                 *  For $nestedKeys = ["address", "home", "city+plot"]
                 */
                foreach ($nestedKeys as $key) {

                    /**
                     *  Does the key exist on $temp:
                     *
                     *  1) array_key_exists('first_name', $temp) = true
                     *
                     *  2) array_key_exists('last_name', $temp) = true
                     *
                     *  3) array_key_exists('address', $temp) = true
                     *     array_key_exists('home', $temp) = true
                     *     array_key_exists('city+plot', $temp) = false
                     */
                    if (!is_null($temp) && array_key_exists($key, $temp)) {

                        /**
                         *  $temp = $temp['first_name'] = "John";
                         *
                         *  $temp = $temp['last_name'] = "Doe";
                         *
                         *  $temp = $temp['address'] = [
                         *      "home" => [
                         *          "city" => "Gaborone",
                         *          "plot" => "Plot 123",
                         *          "street" => "ABC"
                         *      ],
                         *      "work" => [
                         *          "city" => "Gaborone",
                         *          "plot" => "Plot 456",
                         *          "street" => "DEF"
                         *      ]
                         *  ];
                         *
                         *  $temp = $temp['home'] = [
                         *      "city" => "Gaborone",
                         *      "plot" => "Plot 123",
                         *      "street" => "ABC"
                         *  ],
                         *
                         *  Sometimes the value of the $temp[$key] might be a integer,
                         *  string, boolean or array. These are good values, however
                         *  sometimes we might get objects as values e.g Values that
                         *  have been formated using:
                         *
                         *  convertToCurrencyFormat()
                         *  convertToMoneyFormat()
                         *  formatPhoneNumber()
                         *
                         *  These methods are found in the App\Traits\Base\BaseTrait.php file
                         *  and can be used to convert values into objects. Now when we need
                         *  to convert such values into proper arrays to be able to access
                         *  the values using array keys e.g
                         *
                         *  Convert this:
                         *  {
                         *      "amount": 2.0,
                         *      "amountWithoutCurrency": "2.00",
                         *      "amountWithCurrency": "P2.00"
                         *  }
                         *
                         *  Into this:
                         *  [
                         *      "amount" => 2.0,
                         *      "amountWithoutCurrency" => "2.00",
                         *      "amountWithCurrency" => "P2.00"
                         *  ]
                         *
                         *  If we do not do this, then array_key_exists($key, $temp) will throw an error:
                         *
                         *  array_key_exists(): Argument #2 ($array) must be of type array, stdClass given
                         */
                        $temp = $this->convertObjectToArray($temp[$key]);

                    /**
                     *  If the $key = "*", then this is a wild card. This works for instances such as
                     *
                     *  $filter = 'data.*.first_name';
                     *
                     *  $payload = [
                     *      'data' => [
                     *          [
                     *              'first_name' => 'John',
                     *              'last_name' => 'Doe',
                     *          ],
                     *          [
                     *              'first_name' => 'Jane',
                     *              'last_name' => 'Moe',
                     *          ],
                     *          ...
                     *      ]
                     *  ]
                     *
                     *  The we know that $data['*'] does not exist, which is why we are now here.
                     *  We can check if the current $key = '*' so that we can handle the filter
                     *  on multiple entries.
                     */
                    } else if($key == '*') {

                        /**
                         *  Let us check if the the $filter continues anything after the "*" wildcard to specify
                         *  the specific fields that are wanted e.g $filter = 'data.*.first_name'; would mean
                         *  that for every entry we want to extract the "first_name" only.
                         *
                         *  Otherwise if nothing is specified after the "*" wildcard, then we would not
                         *  limit any further but return the values as is e.g $filter = 'data.*';
                         */
                        if( Str::contains($filter, '*.') ) {

                            //  Get the limiter after the first occurance of "*."
                            $subLimiter = Str::after($filter, '*.');

                            //  Replace the occurance of ":" with ","
                            $subLimiter = Str::replace('+', ',', $subLimiter);

                            foreach($temp as $subKey => $subPayload) {

                                //  dd($key, $filter, $subKey, $subPayload, $subLimiter, $this->limitPayload($subPayload, $subLimiter));

                                $temp[$subKey] = $this->limitPayload($subPayload, $subLimiter);

                            }

                            //  Get the limiter before the first occurance of ".*"
                            $subFilter = Str::before($filter, '.*');

                            // Assign the filtered value to the result
                            $result = array_merge_recursive($result, self::convertToNestedArray($subFilter, $temp));

                            /**
                             *  Continue to the next filter in the loop since we are done here
                             */
                            continue(2);

                        }else{

                            //  Get the limiter before the first occurance of ".*"
                            $subFilter = Str::before($filter, '.*');

                            // Assign the filtered value to the result
                            $result = array_merge_recursive($result, self::convertToNestedArray($subFilter, $temp));

                            /**
                             *  Continue to the next filter in the loop since we are done here
                             */
                            continue(2);

                        }

                    } else if(Str::contains($key, '+')) {

                        /**
                         *  If a key doesn't exist e.g
                         *
                         *  isset($temp['city+plot']) = false
                         *
                         *  But contains a "+" symbol e.g
                         *
                         *  $key = 'city+plot'
                         *
                         *  Simply proceed to the next code logic to execute the
                         *  array_merge_recursive() method
                         *
                         *  ------------------------------------------------
                         *
                         *  At this point the the information is as follows:
                         *
                         *  $filter = 'address.home.city+plot';
                         *
                         *  $key = 'city+plot';
                         *
                         *  $temp = [
                         *      "city" => "Gaborone",
                         *      "plot" => "Plot 123",
                         *      "street" => "ABC"
                         *  ];
                         */

                    } else {

                        /**
                         *  If a key doesn't exist e.g
                         *
                         *  isset($temp['first_na_me']) = false
                         *
                         *  Continue to the next filter in the loop since we are done here
                         */
                        continue(2);

                    }
                }

                // Assign the filtered value to the result
                $result = array_merge_recursive($result, self::convertToNestedArray($filter, $temp));

            }

            return $result;

        }else{

            return $payload;

        }
    }

    private function convertObjectToArray($data)
    {
        if(is_object($data)) {

            /**
             *  Convert an object into an array e.g
             *
             *  Convert this:
             *  {
             *      "amount": 2.0,
             *      "amountWithoutCurrency": "2.00",
             *      "amountWithCurrency": "P2.00"
             *  }
             *
             *  Into this:
             *  [
             *      "amount" => 2.0,
             *      "amountWithoutCurrency" => "2.00",
             *      "amountWithCurrency" => "P2.00"
             *  ]
             */
            $data = (array) $data;

            /**
             *  Convert keys to the required format e.g
             *
             *  Convert this:
             *  [
             *      "amount" => 2.0,
             *      "amountWithoutCurrency" => "2.00",
             *      "amountWithCurrency" => "P2.00"
             *  ]
             *
             *  Into this:
             *  [
             *      "amount" => 2.0,
             *      "amount_without_currency" => "2.00",
             *      "amount_with_currency" => "P2.00"
             *  ]
             *
             */
             if($this->isCamelCaseFormat) {

                return $this->payloadNamingConvention->convertToCamelCaseFormat($data);

            }else{

                return $this->payloadNamingConvention->convertToSnakeCaseFormat($data);

            }

        }else{

            return $data;

        }
    }

    /**
     *  Convert flattened key to nested array
     *
     *  -------------------------------------
     *  $filter = "first_name"
     *  $value = "John"
     *  returns "John"
     *  -------------------------------------
     *  $filter = "last_name"
     *  $value = "Doe"
     *  returns "Doe"
     *  -------------------------------------
     *  $filter = "address.home.city+plot"
     *  $value = [
     *      "city" => "Gaborone",
     *      "plot" => "Plot 123",
     *      "street" => "ABC"
     *  ];
     *  returns [
     *      "city" => "Gaborone",
     *      "plot" => "Plot 123"
     *  ];
     */
    private function convertToNestedArray(string $filter, $value): array
    {
        /**
         *  If we have:
         *
         *  $filter = "first_name" then
         *  $dotKeys = ["first_name"];
         *
         *  If we have:
         *
         *  $filter = "last_name" then
         *  $dotKeys = ["last_name"];
         *
         *  If we have:
         *
         *  $filter = "address.home.city+plot" then
         *  $dotKeys = ["address", "home", "city+plot"];
         */
        $dotKeys = explode('.', $filter);

        $result = [];

        /**
         *  Loop from the last item to the first item.
         *  This means that if we have:
         *
         *  $dotKeys = ["address", "home", "city+plot"];
         *
         *  Then we start with $i=2, then $i=1 and then $i=0,
         *  Which means we target
         *
         *  First: $dotKeys[2] = "city+plot"
         *  Then:  $dotKeys[1] = "home"
         *  Then:  $dotKeys[0] = "address"
         *
         */
        for ($i = count($dotKeys) - 1; $i >= 0; $i--) {

            /**
             *  If this is the first dot key we are starting with e.g
             *
             *  This works for:
             *
             *  $dotKeys[$i] = "first_name"
             *  $dotKeys[$i] = "last_name"
             *  $dotKeys[$i] = "city+plot"
             */
            if($i == (count($dotKeys) - 1)) {

                /**
                 *  If we have:
                 *
                 *  $dotKeys[$i] = "first_name" then
                 *  $plusKeys = ["first_name"];
                 *
                 *  If we have:
                 *
                 *  $dotKeys[$i] = "last_name" then
                 *  $plusKeys = ["last_name"];
                 *
                 *  If we have:
                 *
                 *  $dotKeys[$i] = "last_name" then
                 *  $plusKeys = ["city", "plot"];
                 */
                $plusKeys = explode('+', $dotKeys[$i]);

                /**
                 *  If the $plusKeys have only one entry e.g
                 *
                 *  If $plusKeys = ["first_name"];
                 *  If $plusKeys = ["last_name"];
                 */
                if(count($plusKeys) == 1) {

                    /**
                     *  Capture the value as is e.g
                     *
                     *  $result = "John";
                     *  $result = "Doe";
                     */
                    $result = $value;

                /**
                 *  If the $plusKeys have more than one entry e.g
                 *
                 *  If $plusKeys = ["city", "plot"];
                 */
                }else{

                    /**
                     *  Foreach $plusKey key/value pair
                     */
                    foreach($plusKeys as $plusKey) {


                        /**
                         *  Check if the key exists on the value assuming that the value is an array e.g
                         *
                         *  array_key_exists('plot', [
                         *      "city" => "Gaborone",
                         *      "plot" => "Plot 123",
                         *      "street" => "ABC"
                         *  ])
                         *
                         *  or
                         *
                         *  array_key_exists('city', [
                         *      "city" => "Gaborone",
                         *      "plot" => "Plot 123",
                         *      "street" => "ABC"
                         *  ])
                         *
                         *  Incase we have provided a key that does not exist, this check will prevent any errors e.g
                         *
                         *  array_key_exists('country', [
                         *      "city" => "Gaborone",
                         *      "plot" => "Plot 123",
                         *      "street" => "ABC"
                         *  ])
                         */
                        if(array_key_exists($plusKey, $value)) {

                            /**
                             *  Capture the key and value e.g
                             *
                             *  $result['city'] = ['Gaborone'];
                             *  $result['plot'] = ['Plot 123'];
                             */
                            $result[$plusKey] = $value[$plusKey];

                        }

                    }

                }

            }

            /**
             *  If this dot key does not contain any "+"
             *
             *  Allow "first_name", "last_name", "home" and "address"
             *
             *  Ignore "city+plot"
             *
             *  ----------------------------
             *
             *  This prevents the following outcome:
             *
             *  $payload = [
             *      "firstName" => "John",
             *      "lastName" => "Doe",
             *      "address" => [
             *          "home" => [
             *              "city+plot" => [
             *                  "city" => "Gaborone",
             *                  "plot" => "Plot 123"
             *              ]
             *          ]
             *      ],
             *  ];
             *
             *  Notice that the "city+plot" has been used to wrap around the content.
             *  This is not a desired behaviour and the following code prevents it
             *  from happening.
             */
            if(Str::contains($dotKeys[$i], '+') == false) {

                $result = [
                    $dotKeys[$i] => $result
                ];

            }

        }

        return $result;
    }
}
