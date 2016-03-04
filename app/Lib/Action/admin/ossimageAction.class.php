<?php
class ossimageAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index($image='')
    {
    	list($path, $module, $imgMM) = explode( '@', $image );
    	$api_response	= oss_get_image_url( $path, $module, $imgMM );
    	if( $api_response == TRUE )
    	{
    		$contents	= file_get_contents($api_response['decoded_response']['data']['imgUrl']);
    		foreach( $http_response_header AS $header )	header($header);
    		exit( $contents );
    	}
    }
}