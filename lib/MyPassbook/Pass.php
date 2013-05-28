<?php
namespace MyPassbook;
use \Model as Model;
use \ORM as ORM;

class Pass extends Model
{
    public static $_table = 'passes';
    
    /**
     * Implements the belongs_to relationship
     */
    public function subscriber()
    {
        return $this->belongs_to('\MyPassbook\Subscriber', 'subscriber_id');
    }
    
    /**
     * Implements the many to many relationship
     */
    public function devices()
    {
        return $this->has_many_through(
            '\MyPassbook\Device',
            '\MyPassbook\DevicePass',
            'pass_id',
            'device_id'
        );
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
     * Deletes the current pass and linked subscriber and devices
     *
     * @return boolean
     */
    public function delete()
    {
        // The devices link relies on relations, so first find and delete the devices
        if (!$devices = $this->devices()->find_many()->delete()) {
            throw new \Exception('Unable to delete devices for pass with ID ' . $this->id);
        }
        
        // Then delete the relations
        if (!$relations = Model::factory('\MyPassbook\DevicePass')
            ->where('pass_id', $this->id)->find_many()->delete()) {
            throw new \Exception('Unable to delete relations for pass with ID ' . $this->id);
        }
        
        // And finally delete the subscriber (one-to-one)
        if (!$this->subscriber()->find_one()->delete()) {
            throw new \Exception('Unable to delete subscriber for pass with ID ' . $this->id);
        }
        
        // Now we can delete the pass
        return parent::delete();
    }
    
    /**
     * Returns the filename for the current pass
     *
     * The formula is: md5(<ID>|<Email>)
     *
     * @return string
     */
    public function filename()
    {
        $filename = md5($this->id . '|' . $this->email);
        return $filename;
    }
    
    /**
     * Creates a .pkpass package
     * 
     * @param  string  $source    The base directory containing the pass templates
     * @param  string  $dest      The base directory where to save the pass
     * @param  string  $cert      The path to the signer's certificate
     * @param  string  $password  The certificate's password
     * @return void
     * @throws \Exception
     */
    public function pack($source, $dest, $cert, $password)
    {
        // Creates a directory for the pass and copy the necessary files
        // eg. source/MemberID/src
        // The final pass file is saved to source/MemberID.pkpass
        $workDir = "$dest/" . $this->filename();
        // Try to delete if already exists
        if (file_exists($workDir) && is_dir($workDir)) {
            if (!PassSigner::rrmdir($workDir)) {
                throw new \Exception('Unable to remove working directory ' . $workDir);
            }
        }
        
        if (!mkdir($workDir)) {
            throw new \Exception('Unable to create working directory ' . $workDir);
        }
        
        // Copy base files: logos and icons
        $files = array(
            'logo.png',
            'logo@2x.png',
            'icon.png',
            'icon@2x.png',
        );
        foreach ($files as $file) {
            $sourceFile = "$source/" . $this->type . ".raw/$file";
            if (is_readable($sourceFile)) {
                $res = copy($sourceFile, "$workDir/$file");
                if (!$res) {
                    throw new \Exception('Unable to copy file ' . $file);
                }
            }
        }
        // Write JSON file
        $res = file_put_contents("$workDir/pass.json", $this->data);
        if ($res === false) {
            throw new \Exception('Unable to write JSON data');
        }
        
        // Copy thumbnail
        $thumbnail = file_get_contents($this->subscriber()->find_one()->picture);
        $res = file_put_contents("$workDir/thumbnail.png", $thumbnail);
        if ($res === false) {
            throw new \Exception('Unable to write thumbnail data');
        }
        
        $dest = "$dest/" . $this->filename() . '.pkpass';
        
        // Create pass
        PassSigner::signPass($workDir, $cert, $password, $dest, true);
        
        $valid = PassSigner::verifyPassSignature($dest);
        if (!$valid) {
            throw new \Exception('Unable to validate pass');
        }
        // Try to clean
        if (!PassSigner::rrmdir($workDir)) {
            throw new \Exception('Unable to clean working directory ' . $workDir);
        }
    }
}
