<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\Contact
 *
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string $email
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contact whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Contact whereName($value)
 * @mixin \Eloquent
 */
class Contact extends Model
{
    use Notifiable;

    protected $table = 'contacts';
    public $timestamps = false;
}
