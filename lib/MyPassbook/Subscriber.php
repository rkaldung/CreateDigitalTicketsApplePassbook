<?php
namespace MyPassbook;
use \Model as Model;
use \ORM as ORM;
class Subscriber extends Model
{
    public static $_table = 'subscribers';
    
    /**
     * Implements the has_one relationship
     */
    public function pass()
    {
        return $this->has_one('\MyPassbook\Pass', 'subscriber_id');
    }
    /**
     * Overrides default create function, allows an input array
     *
     * @param  array  $data  Associative array with parameters
     * @return void
     */
    public function create($data = array())
    {
        parent::create();
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
    
    /**
     * Creates a pass of the given type linked to the current subscriber
     * 
     * @param  string  $type  Type of pass (eg. Generic, Ticket, ...)
     * @param  array   $data  Associative array of pkpass data
     * @return Pass or false
     */
    public function createPass($type, $data = array())
    {
        try {
            $pass = $this->pass()->create(
                array(
                    'type' => $type,
                    'subscriber_id' => $this->id,
                    'auth_token' => $data['authenticationToken'],
                    'data' => json_encode($data),
                    'created' => $this->created
                )
            );
            if ($pass->save() === true) {
                return $pass;
            }
            
            return false;
        }
        catch (\Exception $e) {
            throw new \Exception('Unable to create pass for user ' . $this->id);
        }
    }
}
