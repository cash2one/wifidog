<?php
class Wechat {

	private $data = array();


	public function __construct($token){
		$this->auth($token) || exit;
		if(IS_GET){
			echo($_GET['echostr']);exit;
		} else {
			$xml = file_get_contents("php://input");
			
			$xml = new SimpleXMLElement($xml);
			$xml || exit;
			
	        foreach ($xml as $key => $value) {
	        	$this->data[$key] = strval($value);
	        }
		}
		
	}

	/**
	 * ��ȡ΢�����͵�����
	 * @return array ת��Ϊ����������
	 */
	public function request(){
       	return $this->data;
	}

	/**
	 * * ��Ӧ΢�ŷ��͵���Ϣ���Զ��ظ���
	 * @param  string $to      �����û���
	 * @param  string $from    �������û���
	 * @param  array  $content �ظ���Ϣ���ı���ϢΪstring����
	 * @param  string $type    ��Ϣ����
	 * @param  string $flag    �Ƿ��±�ս��ܵ�����Ϣ
	 * @return string          XML�ַ���
	 */
	public function response($content, $type = 'text', $flag = 0){
		/* �������� */
		$this->data = array(
			'ToUserName'   => $this->data['FromUserName'],
			'FromUserName' => $this->data['ToUserName'],
			'CreateTime'   => NOW_TIME,
			'MsgType'      => $type,
		);

		/* ����������� */
		$this->$type($content);

		/* ���״̬ */
		$this->data['FuncFlag'] = $flag;

		/* ת������ΪXML */
		$xml = new SimpleXMLElement('<xml></xml>');
		$this->data2xml($xml, $this->data);
		exit($xml->asXML());
	}

	/**
	 * �ظ��ı���Ϣ
	 * @param  string $content Ҫ�ظ�����Ϣ
	 */
	private function text($content){
		$this->data['Content'] = $content;
	}

	/**
	 * �ظ�������Ϣ
	 * @param  string $content Ҫ�ظ�������
	 */
	private function music($music){
		list(
			$music['Title'], 
			$music['Description'], 
			$music['MusicUrl'], 
			$music['HQMusicUrl']
		) = $music;
		$this->data['Music'] = $music;
	}

	/**
	 * �ظ�ͼ����Ϣ
	 * @param  string $news Ҫ�ظ���ͼ������
	 */
	private function news($news){
		$articles = array();
		foreach ($news as $key => $value) {
			list(
				$articles[$key]['Title'],
				$articles[$key]['Description'],
				$articles[$key]['PicUrl'],
				$articles[$key]['Url']
			) = $value;
			if($key >= 9) { break; } //���ֻ����10������
		}
		$this->data['ArticleCount'] = count($articles);
		$this->data['Articles'] = $articles;
	}
	private function transfer_customer_service($content){
		$this->data['Content'] = '';
	}
	
    private function data2xml($xml, $data, $item = 'item') {
        foreach ($data as $key => $value) {
            /* ָ��Ĭ�ϵ�����key */
            is_numeric($key) && $key = $item;

            /* �����Ԫ�� */
            if(is_array($value) || is_object($value)){
                $child = $xml->addChild($key);
                $this->data2xml($child, $value, $item);
            } else {
            	if(is_numeric($value)){
            		$child = $xml->addChild($key, $value);
            	} else {
            		$child = $xml->addChild($key);
	                $node  = dom_import_simplexml($child);
				    $node->appendChild($node->ownerDocument->createCDATASection($value));
            	}
            }
        }
    }

   
	private function auth($token){
		
		$signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		} 
		return true;
	}

}
