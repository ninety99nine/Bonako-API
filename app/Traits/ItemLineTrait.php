<?php

namespace App\Traits;

use App\Models\ProductLine;
use App\Traits\Base\BaseTrait;

trait ItemLineTrait
{
    use BaseTrait;

    /**
     *  Record the detected change on this item line
     *
     *  @param string $changeType
     *  @param string|null $message
     *  @param ProductLine|null $existingItemLine
     */
    public function recordDetectedChange($changeType, $message = null, $existingItemLine = null)
    {
        /**
         *  Check if the user has already been notified about this detected change.
         *  If the existing item line is Null, or the existing item line is present
         *  but already has a detected change that matches the current change then
         *  the user has already been notified otherwise they have not been
         *  notified.
         */
        $notifiedUser = ($existingItemLine === null) ? false : $existingItemLine->hasDetectedChange($changeType);

        $this->detected_changes = collect($this->detected_changes)->push([
            'date' => now(),
            'type' => $changeType,
            'message' => $message,
            'notified_user' => $notifiedUser
        ])->all();

        return $this;
    }

    /**
     *  Return true / false whether the given change type
     *  exists on the item line detected changes
     *
     *  @param string $changeType
     */
    public function hasDetectedChange($changeType)
    {
        return collect($this->detected_changes)->contains(function($detectedChange) use ($changeType){
            return ($detectedChange['type'] == $changeType);
        });
    }

    /**
     *  Empty the detected changes
     */
    public function clearDetectedChanges()
    {
        $this->detected_changes = [];
        return $this;
    }

    /**
     *  Empty the cancellation reasons
     */
    public function clearCancellationReasons()
    {
        $this->cancellation_reasons = [];
        return $this;
    }

    /**
     *  Set the item line as cancelled
     *
     *  @param string|null $cancellationReason
     */
    public function cancelItemLine($cancellationReasons = null)
    {
        $this->is_cancelled = true;

        if( is_string($cancellationReasons) ) {

            $cancellationReasons = [ $cancellationReasons ];

        }elseif( is_null($cancellationReasons) ) {

            $cancellationReasons = [];

        }

        //  Set the message and datetime of each cancellation message
        collect($cancellationReasons)->map(function($cancellationReason) {
            return [
                'date' => now(),
                'message' => $cancellationReason
            ];
        });

        $this->cancellation_reasons = collect($this->cancellation_reasons)->push(...$cancellationReasons)->all();

        return $this;
    }

}
