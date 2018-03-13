<?php

##mongo connectivity
class myprojMongoSingleton
{
    static $db = NULL;
	
	private const token = 'tyxB6WS4CyrX4TfrdY74uSaaeCCJ58sM';
    private const key = 'cMdIgJY7JCmjRUhIyFZSY7weZySNZABy';
   	public static $dbname = 'rp';
    
    static function getMongoCon()
    {
		
        if (self::$db === null)
        {
            try {

                  $manager = new MongoDB\Driver\Manager("mongodb://mongo:27017/".self::$dbname);
				
				  
				  
				  
				} catch (MongoConnectionException $e) {
                die('Failed to connect to MongoDB '.$e->getMessage());
            }
            self::$db = $manager;
        }

        return self::$db;
    }
	
	##check authentication token
	static function checkToken($receivedKey,$receivedToken)
       {

       		if($receivedToken!='' && $receivedToken==self::token && $receivedKey!='' && $receivedKey==self::key)
       		{

       			return 1;
				exit;
       		}

       		else
       		{

       			return 0;
				exit;
       		}
			
		}
		##check type of input data
	static function checkTypeOfInputData($contentType)
       {
       		if($contentType!='' && $contentType=='application/json')
       		{
       			return 1;
				exit;
       		}
       		else
       		{
       			return 0;
				exit;
       		}
			
		}
	}
		
		


	
	
	
	
	
	

