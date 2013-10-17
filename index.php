<?php

require_once('../wp-blog-header.php');
require_once('../wp-includes/query.php');
require_once('API/TwitterAPIExchange.php');

function make_bitly_url($url,$login,$appkey,$format = 'xml',$version = '2.0.1')
{
	//create the URL
	$bitly = 'http://api.bit.ly/shorten?version='.$version.'&longUrl='.urlencode($url).'&login='.$login.'&apiKey='.$appkey.'&format='.$format;
	
	//get the url
	//could also use cURL here
	$response = file_get_contents($bitly);
	
	//parse depending on desired format
	if(strtolower($format) == 'json')
	{
		$json = @json_decode($response,true);
		return $json['results'][$url]['shortUrl'];
	}
	else //xml
	{
		$xml = simplexml_load_string($response);
		return 'http://bit.ly/'.$xml->results->nodeKeyVal->hash;
	}
}

function mensaje(){

	$year = date('Y');
	$mes = date('m');

	query_posts('cat=-13&posts_per_page=1&orderby=rand&year='.$year.'&monthnum='.$mes);
	while(have_posts()): the_post();

	// Ingresa tu nombre de usuario y API key de bit.ly
	$url = make_bitly_url(get_permalink(),'','','json');

	// Categoria
	foreach((get_the_category()) as $category){
		$ncat = str_replace(' ', '', $category->cat_name);
	}

	// contare los caracteres dle titulo

	$contarcaracteres = strlen(the_title('','',false));

	if($contarcaracteres > 100):
		$tweet = substr(the_title('','',false),0,95).'...';
	else:
		$tweet = the_title('','',false);
	endif;

	// Formateando el string de salida
	$mensaje = $tweet.' '.$url.' #Tecnologia #'.$ncat;

	return $mensaje;

	endwhile;
}

$twett = substr(mensaje(),0,140);


// Estos datos son necesarios para conectarse con la API de Twitter
// Crea tu aplicacion en http://dev.twitter.com
$settings = array(
    'oauth_access_token' => "",
    'oauth_access_token_secret' => "",
    'consumer_key' => "",
    'consumer_secret' => ""
);

$url = 'https://api.twitter.com/1.1/statuses/update.json';
$requestMethod = 'POST';
$postfields = array('status' => $twett);
$twitter = new TwitterAPIExchange($settings);
$response =  $twitter->buildOauth($url, $requestMethod)
                 ->setPostfields($postfields)
                 ->performRequest();


?>