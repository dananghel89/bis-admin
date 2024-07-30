<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    const ADMIN = 0;
    const EDITOR = 1;
    const SUBSCRIBER = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get role value from integer.
     */
    public function getRole() {
        $result = '';

        switch ( $this->role ) {
            case 0:
                $result = 'admin';
                break;
            
            case 1:
                $result = 'editor';
                break;

            case 2:
                $result = 'subscriber';
                break;
        }

        return $result;
    }
}
