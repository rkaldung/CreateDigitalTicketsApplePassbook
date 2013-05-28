<?php
namespace MyPassbook;
use \Model as Model;
use \ORM as ORM;

class Device extends Model
{
    public static $_table = 'devices';
    
    /**
     * Implements the many to many relationship
     */
    public function passes()
    {
        return $this->has_many_through(
            '\MyPassbook\Pass',
            '\MyPassbook\DevicePass',
            'device_id',
            'pass_id'
        );
    }
}
