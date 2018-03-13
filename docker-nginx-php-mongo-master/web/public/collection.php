<?php

/**
*Collection class handeling the operation on "Recepies" collection in mongodb "Database"
**/
class Collection 
{
	/**
	*variable selct the database
	**/
	public $connection;

	/**
	*variable selct the collection in the mongodb "Database"
	**/
	public $collection;

	/**
	*Constructor of class Collection
	* @param $con 
	**/

	function __construct($connection)
	{ 
		$this->collection = $connection;
	}

	/** Method insert data into "Recipes" Collection**/
	public function saveRecipe($recipeArray,$dbname)
	{
		try
		{
			$recipeName = isset($recipeArray['recipeName'])?$recipeArray['recipeName']:''; 
			$preparation = isset($recipeArray['preparation'])?$recipeArray['preparation']:''; 
			$difficulty  = isset($recipeArray['difficulty'])?$recipeArray['difficulty']:''; 
			$vegetarian  = isset($recipeArray['vegetarian'])?$recipeArray['vegetarian']:''; 
			$preparartionTime = isset($recipeArray['preparartionTime'])?$recipeArray['preparartionTime']:''; 
			$ingredients = isset($recipeArray['ingredients'])?$recipeArray['ingredients']:''; 
			$tools = isset($recipeArray['tools'])?$recipeArray['tools']:''; 
			$nutrition = isset($recipeArray['nutrition'])?$recipeArray['nutrition']:''; 
			$rid='r_'.time();
			if($recipeArray!='' && $preparation!='' && $difficulty!=''&& $vegetarian!=''&& $preparartionTime!=''&& $ingredients!=''&& $tools!=''&& $preparation!=''&& $nutrition!='')
			{
				$bulk = new MongoDB\Driver\BulkWrite();

				//prepare array to save in db 
				$bulk->insert(['rid'=>$rid,'recipeName' =>$recipeName, 'preparation'=>$preparation,'difficulty'=>$difficulty, 'vegetarian'=>$vegetarian,'prepTime'=>$preparartionTime, 'ingredients'=>$ingredients,'tools'=>$tools, 'nutrition'=>$nutrition]);
				
				$writeConcern = new MongoDB\Driver\writeConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
				$result =  $this->collection->executeBulkWrite($dbname.'.Recipes', $bulk);

				return json_encode(array('message'=>'Recipe saved successfully.','status'=>'1'));
				exit;
			}
			else
			{
				return json_encode(array('message'=>'Please provide all parametres','status'=>'0'));
				exit;
			}
			
		}
		catch(exception $e)
		{
			$msg= $e->getMessage();
			return json_encode(array('message'=>$msg,'status'=>'0'));
			exit;
		}
		

  	   
	}
	/** Method insert data into "Rating" Collection**/

	public function saveRating($ridParam,$recipeArray,$dbname)
	{
		try
		{	
			$rid = isset($ridParam)?$ridParam:''; //Recipe id
			$deviceId = isset($recipeArray['deviceId'])?$recipeArray['deviceId']:''; //deviceid
			$rating  = isset($recipeArray['rating'])?$recipeArray['rating']:''; //rating
			
			if($rid!='' && $deviceId!='' && $rating!='')
			{
				//check if rating for same device id already exists.
				$filter = ['deviceId' => $deviceId,'rid'=>$rid];
				$options = [
			     "limit" => 1
				];
				
				$queryToCheckRating = new MongoDB\Driver\Query($filter,$options);
				$ratingsExist = $this->collection->executeQuery($dbname.'.Rating', $queryToCheckRating);
				//The toArray() method returns an array containing all results for this cursor and the current() function returns the array's current element
				$ratingsExistArray = current($ratingsExist->toArray());
				if(!empty($ratingsExistArray) && $ratingsExistArray->deviceId!='' && $ratingsExistArray->deviceId==$deviceId)
				{
					return json_encode(array('message'=>'Rating already exist for this device.','status'=>'0'));
					exit;
				}
				else if($rating<=0 || $rating>5 || !is_numeric( $rating )) 
				{
					return json_encode(array('message'=>'Invalid rating.','status'=>'0'));
					exit;
				}
				else
				{
					$bulk = new MongoDB\Driver\BulkWrite();
					//prepare array to save in db 
					$bulk->insert(['rid'=>$rid,'deviceId' =>$deviceId, 'rating'=>$rating]);
				
					$writeConcern = new MongoDB\Driver\writeConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
				
					$result =  $this->collection->executeBulkWrite($dbname.'.Rating', $bulk);

					return json_encode(array('message'=>'Rating saved successfully.','status'=>'1'));
					exit;
				}
				
			}
			else
			{
				return json_encode(array('message'=>'Please provide all parametres','status'=>'0'));
				exit;
			}
			
		}
		catch(exception $e)
		{
			$msg= $e->getMessage();
			return json_encode(array('message'=>$msg,'status'=>'0'));
			exit;
		}
		

  	   
	}

	/** Method to retrive Recipe by id from "Rating" Collection**/
	public function getAllRecipes($pagination,$dbname)
	{
		try
		{
			if($pagination=='' || $pagination=='page' || $pagination==0)
			{
				$pagination=1;
			}
			$limit =10; //default limit
			$skip = ($pagination-1)*$limit;//no. of documents to skip
			$filter = [];
			$options = ["limit" => $limit,"skip"=>$skip,"sort" => [ '_id' => -1 ]];
				
			$queryToCheckRecipes = new MongoDB\Driver\Query($filter,$options);
		
			$recipeExist = $this->collection->executeQuery($dbname.'.Recipes', $queryToCheckRecipes);
			//The toArray() method returns an array containing all results for this cursor and the current() function returns the array's current element

			$recipeExistArray = $recipeExist->toArray();
			if(!empty($recipeExistArray))
			{
				return json_encode(array('data'=>$recipeExistArray,'status'=>'1'));
				exit;
			}
			else
			{
				return json_encode(array('message'=>'No match found','status'=>'0'));
				exit;
			}
			
			
			
		}
		catch(exception $e)
		{
			$msg= $e->getMessage();
			return json_encode(array('message'=>$msg,'status'=>'0'));
			exit;
		}
	}
	/** Method to retrive Recipe by id from "Rating" Collection**/
	public function getRecipe($recipeId,$dbname)
	{
		try
		{
			if($recipeId!='')
			{
				//check if recipe exists using rid.
				$filter = ['rid' => $recipeId];
				$options = [
			     "limit" => 1
				];
				
				$queryToCheckRecipe = new MongoDB\Driver\Query($filter,$options);
				$recipeExist = $this->collection->executeQuery($dbname.'.Recipes', $queryToCheckRecipe);
				//The toArray() method returns an array containing all results for this cursor and the current() function returns the array's current element
				$recipeExistArray = current($recipeExist->toArray());
				if(!empty($recipeExistArray))
				{
					return json_encode(array('data'=>$recipeExistArray,'status'=>'1'));
					exit;
				}
				else
				{
					return json_encode(array('message'=>'No match found','status'=>'0'));
					exit;
				}
			}
			else
			{
				return json_encode(array('message'=>'Please provide recipe id.','status'=>'0'));
				exit;
			}
			
		}
		catch(exception $e)
		{
			$msg= $e->getMessage();
			return json_encode(array('message'=>$msg,'status'=>'0'));
			exit;
		}
	}

	/**
	* This method update the documents of Recipe that already added in collection
	**/
	public function updateRecipe($rid,$recipeArray,$dbname)
	{

		try
		{
			$recipeName = isset($recipeArray['recipeName'])?$recipeArray['recipeName']:''; 
			$rid = isset($rid)?$rid:''; 
			$preparation = isset($recipeArray['preparation'])?$recipeArray['preparation']:''; 
			$difficulty  = isset($recipeArray['difficulty'])?$recipeArray['difficulty']:''; 
			$vegetarian  = isset($recipeArray['vegetarian'])?$recipeArray['vegetarian']:''; 
			$preparartionTime = isset($recipeArray['preparartionTime'])?$recipeArray['preparartionTime']:''; 
			$ingredients = isset($recipeArray['ingredients'])?$recipeArray['ingredients']:''; 
			$tools = isset($recipeArray['tools'])?$recipeArray['tools']:''; 
			$nutrition = isset($recipeArray['nutrition'])?$recipeArray['nutrition']:''; 
			
			if($rid!='' && $recipeArray!='' && $preparation!='' && $difficulty!=''&& $vegetarian!=''&& $preparartionTime!=''&& $ingredients!=''&& $tools!=''&& $preparation!=''&& $nutrition!='')
			{
				$bulk = new MongoDB\Driver\BulkWrite();
				//prepare array to update document in db 
				$bulk->update(['rid' =>$rid],['$set' => ['recipeName' =>$recipeName, 'preparation'=>$preparation,'difficulty'=>$difficulty, 'vegetarian'=>$vegetarian,'prepTime'=>$preparartionTime, 'ingredients'=>$ingredients,'tools'=>$tools, 'nutrition'=>$nutrition]]);
				
				$writeConcern = new MongoDB\Driver\writeConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
				$result =  $this->collection->executeBulkWrite($dbname.'.Recipes', $bulk);
				
				if($result->getMatchedCount()>0 && $result->getModifiedCount()==0)
				{
					return json_encode(array('message'=>'Match found nothing updated.','status'=>'1'));
					exit;
				}
				else if($result->getModifiedCount()>0 && $result->getMatchedCount()>0)
				{
					return json_encode(array('message'=>'Recipe updated successfully.','status'=>'1'));
					exit;
				}
				else if($result->getMatchedCount()==0)
				{
					return json_encode(array('message'=>'Recipe update failure.','status'=>'0'));
					exit;
				}
				
				return json_encode(array('message'=>'Recipe updated successfully.','status'=>'1'));
				exit;
			}
			else
			{
				return json_encode(array('message'=>'Please provide all parametres','status'=>'0'));
				exit;
			}
			
		}
		catch(exception $e)
		{
			$msg= $e->getMessage();
			return json_encode(array('message'=>$msg,'status'=>'0'));
			exit;
		}
	}

	/** Method to retrive Recipe by id from "Rating" Collection**/
	public function deleteRecipe($recipeId,$dbname)
	{
		try
		{
			if($recipeId!='')
			{
				$bulk = new MongoDB\Driver\BulkWrite();
				//prepare array to remove document 
				$bulk->delete(['rid'=>$recipeId]);
			
				$writeConcern = new MongoDB\Driver\writeConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
				$result =  $this->collection->executeBulkWrite($dbname.'.Recipes', $bulk);

				if($result->getDeletedCount()>0)
				{
					return json_encode(array('message'=>'Recipe removed successfully.','status'=>'1'));
					exit;
				}
				else
				{
					return json_encode(array('message'=>'Recipe remove failure.','status'=>'0'));
					exit;
				}
				
			}
			else
			{
				return json_encode(array('message'=>'Please provide recipe id.','status'=>'0'));
				exit;
			}
		}
		catch(exception $e)
		{
			$msg= $e->getMessage();
			return json_encode(array('message'=>$msg,'status'=>'0'));
			exit;
		}
		
	}

}

?>