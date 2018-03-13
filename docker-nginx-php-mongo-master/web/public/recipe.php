<?php 
require 'connection.php';
require 'collection.php';

	/**
 	*Call the method connect from the MongoConnection class
 	**/
 	
 	$dbname = myprojMongoSingleton::$dbname;
 	$mongoConnection = myprojMongoSingleton::getMongoCon();
	$collection = new Collection($mongoConnection);

	$jsonArray = file_get_contents("php://input"); //get json input

	$recipeArray = json_decode($jsonArray,'true'); //decode json data

    $token = $_SERVER["HTTP_AUTHORIZATION"]; //get token

    $key = $_SERVER['HTTP_X_API_KEY']; //get key

    $contentType = $_SERVER["CONTENT_TYPE"]; //get type of input data

    $requestType = $_SERVER['REQUEST_METHOD']; //get type of request method

	switch ($requestType) {
	    case 'POST':
	        handlePostRequests($key,$token,$contentType,$recipeArray,$dbname,$collection);
	        break;
	    case 'GET':
	        handleGetRequests($dbname,$collection);
	        break;
        case 'PUT':
        	handlePutOrPatchRequests($key,$token,$contentType,$recipeArray,$dbname,$collection);
        break;
         case 'PATCH':
        	handlePutOrPatchRequests($key,$token,$contentType,$recipeArray,$dbname,$collection);
        break;
        case 'DELETE':
        	handleDeleteRequests($key,$token,$dbname,$collection);
        break;
	    default:
	    	handleInvalidRequests();
	    	break;
	       
		}

		//authenticate post requests
	function handlePostRequests($key,$token,$contentType,$recipeArray,$dbname,$collection) 
	{
		
		$recipesParam = isset($_REQUEST['recipes'])?$_REQUEST['recipes']:'';
		$ridParam = isset($_REQUEST['rid'])?$_REQUEST['rid']:'';
        $pageType = isset($_REQUEST['pageType'])?$_REQUEST['pageType']:'';
		
		if($recipesParam=='recipes' && $ridParam=='' && $pageType=='')
		{

			//check if token matches
			$checkTokenRes = myprojMongoSingleton::checkToken($key,$token); 
			//check if type of input data is json
			$checkTypeOfInputDataRes = myprojMongoSingleton::checkTypeOfInputData($contentType);
			//if token is authenticated and type of data is json then call function to save Recipe
			if($checkTokenRes==1 && $checkTypeOfInputDataRes==1)
			{
				$response=$collection->saveRecipe($recipeArray,$dbname);

				echo $response;
				exit;
			}
			else if($checkTokenRes==0)
			{
				echo json_encode(array('message'=>'Authentication failed.','status'=>'0'));
				exit;
			}
			else if($checkTypeOfInputDataRes==0)
			{
				echo json_encode(array('message'=>'Type of data must be json.','status'=>'0'));
				exit;
			}

		}
		else if($recipesParam=='recipes' && $ridParam!='' && $pageType=='ratings')
		{

			//check if type of input data is json
			$checkTypeOfInputDataRes = myprojMongoSingleton::checkTypeOfInputData($contentType);
			//if token is authenticated and type of data is json then call function to save Recipe
			if($checkTypeOfInputDataRes==1)
			{
				$response=$collection->saveRating($ridParam,$recipeArray,$dbname);

				echo $response;
				exit;
			}
			else if($checkTypeOfInputDataRes==0)
			{
				echo json_encode(array('message'=>'Type of data must be json.','status'=>'0'));
				exit;
			}
		}
		else
		{
			echo json_encode(array('message'=>'Please select a valid method type.','status'=>'0'));
			exit;
		}
	}

      //authenticate get requests
	function handleGetRequests($dbname,$collection) 
	{
		$recipesParam = isset($_REQUEST['recipes'])?$_REQUEST['recipes']:'';
		$ridParam = isset($_REQUEST['rid'])?$_REQUEST['rid']:'';
        $pageType = isset($_REQUEST['pageType'])?$_REQUEST['pageType']:'';
		
		//if pagetype is empty then call function to get detail of single Recipe
		if($recipesParam=='recipes' && $ridParam!='' && $ridParam!='page' && $pageType!='page')
		{
			
			$response = $collection->getRecipe($ridParam,$dbname);
			echo $response;
			exit;
		}
		//if pagetype is given then call function to get all Recipes with pagination
		else if($recipesParam=='recipes' && ($ridParam!='' || $ridParam=='page' || $pageType=='page'))
		{
			
			$response = $collection->getAllRecipes($ridParam,$dbname);
			echo $response;
			exit;
		}
		
		
	}

		//authenticate delete requests
	function handleDeleteRequests($key,$token,$dbname,$collection)
	{
		$rid = $_REQUEST['rid'];
		//check if token matches
		$checkTokenRes = myprojMongoSingleton::checkToken($key,$token); 
		
		//if token is authenticated  call function to deleteRecipe
		if($checkTokenRes==1)
		{
			$response=$collection->deleteRecipe($rid,$dbname);

			echo $response;
			exit;
		}
		else if($checkTokenRes==0)
		{
			echo json_encode(array('message'=>'Authentication failed.','status'=>'0'));
			exit;
		}
		
	}

		//authenticate put or patch requests
	function handlePutOrPatchRequests($key,$token,$contentType,$recipeArray,$dbname,$collection)
	{
		$rid = $_REQUEST['rid'];
		//check if token matches
		$checkTokenRes = myprojMongoSingleton::checkToken($key,$token); 
		//check if type of input data is json
		$checkTypeOfInputDataRes = myprojMongoSingleton::checkTypeOfInputData($contentType);
		//if token is authenticated and type of data is json then call function to save Recipe
		if($checkTokenRes==1 && $checkTypeOfInputDataRes==1)
		{
			$response=$collection->updateRecipe($rid,$recipeArray,$dbname);

			echo $response;
			exit;
		}
		else if($checkTokenRes==0)
		{
			echo json_encode(array('message'=>'Authentication failed.','status'=>'0'));
			exit;
		}
		else if($checkTypeOfInputDataRes==0)
		{
			echo json_encode(array('message'=>'Type of data must be json.','status'=>'0'));
			exit;
		}
	}

		//authenticate other requests
	function handleInvalidRequests()
	{
		echo json_encode(array('message'=>'Please select a valid method type.','status'=>'0'));
		exit;
	}

	
	



?>


