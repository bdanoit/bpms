<?
class ControllerCron extends Controller
{
	public function __before(){
        $this->construct = false;
        header('Content-type: text/plain');
        $this->template = 'blank';
        $this->view = view()->cron;
	}
    
    public function index(){
        return "No habla ingles";
    }
    
    public function task_email_alerts(){
        $sent = array();
        $alerts = run()->manager->taskEmailAlerts->listOlder();
        if(!$alerts) return false;
        require_once(LIB_DIR.'/class.phpmailer.php');
        foreach($alerts as $alert){
            switch($alert->type){
                case 0:
                    $title = "BPMS - Task overdue!";
                    $message = $this->view->task_email_alert(array(
                        "alert"=>$alert,
                        "message"=>"this task is overdue! It was due on $alert->end_pretty."
                    ));
                break;
                case 1:
                    $title = "BPMS - Task due soon!";
                    $message = $this->view->task_email_alert(array(
                        "alert"=>$alert,
                        "message"=>"this task due soon! It is due on $alert->end_pretty."
                    ));
                break;
                case 2:
                    $title = "BPMS - Task complete!";
                    $message = $this->view->task_email_alert(array(
                        "alert"=>$alert,
                        "message"=>"this task has just been marked as complete!"
                    ));
                break;
            }
            
            if($message){
                $mail = new PHPMailer();
                $body = $message;
                $mail->IsSMTP();
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = "tls";
                $mail->Host = "smtp.gmail.com";
                $mail->Port = 587;
                $mail->Username = "bpms@danoit.com";
                $mail->Password = "safe1belize2";

                $mail->SetFrom('bpms@danoit.com', 'BPMS');
                $mail->AddReplyTo("no-reply@danoit.com","no reply");
                $mail->Subject = $title;
                $mail->AltBody = "You must enable HTML emails to see this message...";
                $mail->MsgHTML($body);

                $mail->AddAddress($alert->email, $alert->user_name);

                if(!$mail->Send()) {
                    echo "Mailer Error: " . $mail->ErrorInfo;
                } else {
                    run()->manager->taskEmailAlerts->deleteBy(array(
                        "project_id"=>$alert->project_id,
                        "task_id"=>$alert->task_id,
                        "type"=>$alert->type
                    ));
                    echo "Message sent!";
                }
    
            }
        }
    }
}
