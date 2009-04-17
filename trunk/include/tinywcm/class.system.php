<?

class iSystem {
	var $url;	
	
	function __construct($url,$theme) {
		$this->url=$url;
	}
	
	function goPageDelay($uri,$time=1){
		?>
		<meta http-equiv="refresh" content="<?=$time; ?>;URL=<?=$this->url."/".$uri; ?>" />
		<?
	}
	
	function showMessage($type=1,$message){
		?>
		<div id="messagebox">
			<?
				switch ($type) {
					case "1" : ?><img src="<?=$this->url;?>/image/messagebox_critical.png" border="0" align="absmiddle"><?
					break;
					case "2" : ?><img src="<?=$this->url;?>/image/messagebox_warning.png" border="0" align="absmiddle""><?
					break;
					default:
						?><img src="<?=$this->url;?>/image/messagebox_info.png" border="0" align="absmiddle""><?
					break;
				}
			
			?>
			<?=$message;?>
		</div>
		<?
	}
	
	function setSession($fullname,$memid,$email){
		$_SESSION['memid']=$memid;
		$_SESSION['fullname']=$fullname;
		$_SESSION['email']=$email;
	}
	
	function delSession() {
		$_SESSION['memid']=0;
		$_SESSION['fullname']="";
		$_SESSION['email']="";
	}
	
}

?>