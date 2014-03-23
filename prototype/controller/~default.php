<?
class ControllerDefault extends Controller
{
	public function __before(){
		$this->action = str_replace("_", "-", router::Current()->method);
		$this->page = run()->manager->page->findBy(array("url"=>$this->action));
		if($this->page){
			_global()->tags = $this->page->tags;
			_global()->title = $this->page->title;
		}
		auth::define(array(
			"client_area"=>auth::USER
		));
	}
	
	public function __call($method, $args){
		$params = array(
			"key"=>$this->action
		);
		if($this->page){
			$params["content"] = $this->page->content;
		}
		return view()->page($params);
	}
    
    public function about_us(){
		$folder = run()->manager->folder->findBy(array("name"=>"About Us"));
		if($folder) $images = run()->manager->file->limit(3)->listBy(array("folder_id"=>$folder->id));
		return view()->about_us(array(
			"images"=>$images,
			"key"=>$this->action,
			"content"=>$this->page->content
		));
    }
    
	public function creative_solutions(){
		$folder = run()->manager->folder->findBy(array("name"=>"Creative Solutions"));
		if($folder) $images = run()->manager->file->limit(25)->listBy(array("folder_id"=>$folder->id));
		return view()->creative_solutions(array(
			"images"=>$images,
			"key"=>$this->action,
			"content"=>$this->page->content
		));
	}
	
	public function print_solutions(){
		return view()->print_solutions(array(
			"key"=>$this->action,
			"content"=>$this->page->content
		));
	}
	
	public function contact_us(){
		return view()->contact_us(array(
			"key"=>$this->action,
			"content"=>$this->page->content
		));
	}
	
	public function client_login(){
		if(auth::user()){
			util::Redirect(router::URL('/client-area'));
		}
		$post = vars::post();
		if($post->user){
			if(run()->manager->user->tryLogin($post->user))
				util::Redirect(router::URL('/client-area'));
		}
		return view()->client_login(array(
			"key"=>$this->action,
			"content"=>$this->page->content,
			"user"=>(object)$post->user
		));
	}
	
	public function logout(){
		auth::logout();
		util::Redirect(router::URL('/client-login'));
	}
	
	public function client_area(){
		$user = auth::user();
		$get = vars::get();
		$gallery = run()->manager->gallery->findBy(array("user_id"=>$user->id));
		if(!$gallery){
			if($user->group_id)
				$gallery = run()->manager->gallery->findBy(array("group_id"=>$user->group_id));
		}
		if($user && $user->group_id){
			$group = run()->manager->group->findBy(array("id"=>$user->group_id));
		}
		else if($gallery && $gallery->group_id){
			$group = run()->manager->group->findBy(array("id"=>$gallery->group_id));
		}
		if($get->search)
			$images = run()->manager->gallerySearch->find($get->search, $gallery->id, 5);
		else{
			if($gallery){
				$gallery->folders = run()->manager->gallery->listBy(array("parent_id"=>$gallery->id));
				$gallery->images = run()->manager->galleryImage->listBy(array("gallery_id"=>$gallery->id));
			}
		}
		return view()->client_area(array(
			"gallery"=>$gallery,
			"group"=>$group,
			"user"=>$user,
			"images"=>$images,
			"search"=>$get->search,
			"key"=>$this->action,
			"content"=>$this->page->content,
			"images"=>$images
		));
	}
	
	public function ftp_upload(){
		$post = vars::post();
		if($post->auth){
			$auth = (object)$post->auth;
			$host = 'clientftp.rebootcreative.com';
			$conn_id = @ftp_connect($host, 21);
			$login = @ftp_login($conn_id, $auth->username, $auth->password);
			if(!$conn_id) $error = "Failed to connect to FTP";
			else if(!$login) $error = "Invalid Credentials";
		}
		return view()->ftp_upload(array(
			"content"=>$this->page->content,
			"auth"=>$auth,
			"error"=>$error
		));
	}
	
	private function mailer(){
		require_once(LIB_DIR."/class.phpmailer.php");
		$mail = new PHPMailer();
		
			
		/*
		$mail->IsSMTP();
		$mail->SMTPDebug = 0;
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = 'ssl';
		$mail->Host = 'smtp.gmail.com';
		$mail->AddAddress('bdanoit@gmail.com', "Baleze Danoit");
		$mail->Port = 465;
		$mail->Username = 'bdanoit@gmail.com';
		$mail->Password = 'pourez22';
		*/
		
		$mail->IsSendmail();
		$mail->AddAddress('info@rebootcreative.com', 'Reboot Creative');
		
		return $mail;
	}
	
	public function request_a_quote(){
		$post = vars::post();
		if($post->quote){
			$quote = (object)$post->quote;
			$errors = array();
			$info = (object)array(
				"name"=>
					formAuth::Required,
				"company"=>
					formAuth::Required,
				"city"=>
					formAuth::Required,
				"phone"=>
					formAuth::Required,
				"email"=>
					formAuth::Email | 
					formAuth::Required
			);
			foreach($quote as $key => $value){
				$name = string::keyToName($key);
				$result = formAuth::check($info->$key, $value, $name);
				if($result){
					$errors[] = $result;
				}
			}
			
			if(!$errors){
			
			$mail = $this->mailer();
			
			$count = run()->manager->var->findBy(array("key"=>"quote_count"))->value;
			
			$mail->SetFrom($quote->email, $quote->name);
			$mail->Subject = "Quote Request #$count - $quote->company";
			$Body = <<< MAIL
$quote->name has requested a design quote.

Company:
	$quote->company

City:
	$quote->city

Province:
	$quote->province

Phone Number:
	$quote->phone

Email:
	$quote->email


MAIL;
			if($quote->design_quote) $Body.= <<< MAIL
PRINT QUOTE

$quote->design_description


MAIL;
			if($quote->print_quote) $Body.= <<< MAIL
Print Quote

Product 1:
	$quote->product1

Finished Size 1:
	$quote->finished_size1

Colour Specification 1:
	$quote->colour_spec1

Quantitiy 1:
	$quote->quantity1

Product 2:
	$quote->product2

Finished Size 2:
	$quote->finished_size2

Colour Specification 2:
	$quote->colour_spec2

Quantitiy 2:
	$quote->quantity2



Project Notes / Special Requests:

$quote->notes


MAIL;
			if($quote->web_quote) $Body.= <<< MAIL
WEB QUOTE

$quote->web_description


MAIL;
			$mail->Body = $Body;
			if($errors){
				//Do Nothin
			} if(!$mail->Send()) {
				$errors[] = (object)array("message"=>"Mailer Error: " . $mail->ErrorInfo);
			} else {
				//$errors[] = (object)array("message"=>"Message sent!");
				
				//Add one to quote count
				run()->manager->var->plusplus('quote_count');
				
				//Send mail to sender
				$mail2sender = new PHPMailer();
				$mail2sender->IsSendmail();
				$mail2sender->AddAddress($quote->email, $quote->name);
				$mail2sender->SetFrom('info@rebootcreative.com', 'Reboot Creative');
				$mail2sender->Subject = "Quote Request (RebootCreative)";
				$mail2sender->Body = <<< MAIL
Thank you for your quote request. A representative from Reboot Creative will contact you shortly. Have a great day!
MAIL;
				$mail2sender->Send();
				header('Location: /request-sent');
			}
			}
		}
		return view()->request_a_quote(array(
			"quote"=>(object)$post->quote,
			"key"=>$this->action,
			"errors"=>$errors
		));
	}
	
	public function process_files($username = null, $password = null){
		$POST_MAX_SIZE = ini_get('post_max_size');
		$unit = strtoupper(substr($POST_MAX_SIZE, -1));
		$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

		if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
			header("HTTP/1.1 500 Internal Server Error"); // This will trigger an uploadError event in SWFUpload
			echo "POST exceeded maximum allowed size.";
			exit(0);
		}

		// Settings
		$save_path = "/var/www/uploads/";				// The path were we will save the file (getcwd() may not be reliable and should be tested in your environment)
		$upload_name = "Filedata";
		$max_file_size_in_bytes = 4294967295;				// 2GB in bytes 4294967295
		$extension_whitelist = array("jpg", "gif", "png");	// Allowed file extensions
		$valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';				// Characters allowed in the file name (in a Regular Expression format)
		
		// Other variables	
		$MAX_FILENAME_LENGTH = 260;
		$file_name = "";
		$file_extension = "";
		$uploadErrors = array(
			0=>"There is no error, the file uploaded with success",
			1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
			2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
			3=>"The uploaded file was only partially uploaded",
			4=>"No file was uploaded",
			6=>"Missing a temporary folder"
		);


		// Validate the upload
		if (!isset($_FILES[$upload_name])) {
			print("No upload found in \$_FILES for " . $upload_name);
			exit(0);
		} else if (isset($_FILES[$upload_name]["error"]) && $_FILES[$upload_name]["error"] != 0) {
			print($uploadErrors[$_FILES[$upload_name]["error"]]);
			exit(0);
		} else if (!isset($_FILES[$upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$upload_name]["tmp_name"])) {
			print("Upload failed is_uploaded_file test.");
			exit(0);
		} else if (!isset($_FILES[$upload_name]['name'])) {
			print("File has no name.");
			exit(0);
		}
		
		// Validate the file size (Warning: the largest files supported by this code is 2GB)
		$file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
		if (!$file_size || $file_size > $max_file_size_in_bytes) {
			print("File exceeds the maximum allowed size");
			exit(0);
		}
		
		if ($file_size <= 0) {
			print("File size outside allowed lower bound");
			exit(0);
		}


		// Validate file name (for our purposes we'll just remove invalid characters)
		$file_name = preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', "", basename($_FILES[$upload_name]['name']));
		if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
			print("Invalid file name");
			exit(0);
		}


		// Validate that we won't over-write an existing file
		if (file_exists($save_path . $file_name)) {
			print("File with this name already exists");
			exit(0);
		}

		// Validate file extension
		/*
		$path_info = pathinfo($_FILES[$upload_name]['name']);
		$file_extension = $path_info["extension"];
		$is_valid_extension = false;
		foreach ($extension_whitelist as $extension) {
			if (strcasecmp($file_extension, $extension) == 0) {
				$is_valid_extension = true;
				break;
			}
		}
		if (!$is_valid_extension) {
			print("Invalid file extension");
			exit(0);
		}*/

		// Validate file contents (extension and mime-type can't be trusted)
		/*
			Validating the file contents is OS and web server configuration dependant.  Also, it may not be reliable.
			See the comments on this page: http://us2.php.net/fileinfo
			
			Also see http://72.14.253.104/search?q=cache:3YGZfcnKDrYJ:www.scanit.be/uploads/php-file-upload.pdf+php+file+command&hl=en&ct=clnk&cd=8&gl=us&client=firefox-a
			 which describes how a PHP script can be embedded within a GIF image file.
			
			Therefore, no sample code will be provided here.  Research the issue, decide how much security is
			 needed, and implement a solution that meets the needs.
		*/

		
		// Process the file
		/*
			At this point we are ready to process the valid file. This sample code shows how to save the file. Other tasks
			 could be done such as creating an entry in a database or generating a thumbnail.
			 
			Depending on your server OS and needs you may need to set the Security Permissions on the file after it has
			been saved.
		*/
		$location = $_FILES[$upload_name]["tmp_name"];
		$hash = string::Random(32);
		$destination = '/tmp/'.$hash;
		move_uploaded_file($location, $destination);
		$result = $this->_ftp($destination, $file_name, $username, $password);
		print "\n".$result;
		if($result !== true){
			header("HTTP/1.1 403 Forbidden");
		}
		unlink($destination);
		/*
		$user = auth::user();
		if(!$user){
			print("Not authorized");
			exit(0);
		}
		$folder = UPLOAD_DIR."/{$user->email}";
		if(!is_dir($folder)) mkdir($folder);
		$destination = "{$folder}/{$file_name}";
		$count = 2;
		while(file_exists($destination)){
			$regex = '#\.[^\.]+$#';
			preg_match($regex, $file_name, $match);
			list($ext) = $match;
			$new_name = preg_replace($regex, '', $file_name);
			$destination = $new_name."($count)".$ext;
			$count++;
		}
		move_uploaded_file($location, $destination);
		*/
		exit(0);
	}
	
	private function _ftp($local_file, $ftp_path, $usr = null, $pwd = null){
		// FTP access parameters
		$host = 'clientftp.rebootcreative.com';
		
		if(!$usr){
			return "No user specified";
		}
		 
		// file to move:
		//$local_file = './example.txt';
		 
		// connect to FTP server (port 21)
		$conn_id = ftp_connect($host, 21);
		if(!$conn_id) return 'Cannot connect';
		 
		// send access parameters
		$login = ftp_login($conn_id, $usr, $pwd);
		if(!$login) return 'Cannot login';
		 
		// turn on passive mode transfers (some servers need this)
		ftp_pasv ($conn_id, true);
		 
		// perform file upload
		$ftp_path = '/'.$ftp_path;
		print "\n".$login;
		print "\n".$ftp_path;
		print "\n".$local_file;
		$upload = ftp_put($conn_id, $ftp_path, $local_file, FTP_BINARY);
		 
		// check upload status:
		if (!$upload) return 'Cannot upload';
		 
		/*
		** Chmod the file (just as example)
		*/
		 
		// If you are using PHP4 then you need to use this code:
		// (because the "ftp_chmod" command is just available in PHP5+)
		if (!function_exists('ftp_chmod')) {
		   function ftp_chmod($ftp_stream, $mode, $filename){
				return ftp_site($ftp_stream, sprintf('CHMOD %o %s', $mode, $filename));
		   }
		}
		 
		// try to chmod the new file to 666 (writeable)
		if (ftp_chmod($conn_id, 0666, $ftp_path) !== false) {
			ftp_close($conn_id);
			return true;
		} else {
			ftp_close($conn_id);
			return true;
		}
	}
}