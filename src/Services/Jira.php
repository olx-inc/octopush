<?php

namespace Services;

class Jira {
// extends SoapClient
	protected $token;
  protected $jira_url;
	protected $jira_user;
	protected $jira_pwd;

  const PRODUCTION = "production";

  const READY_TO_LIVE = 10030;
  const DEPLOYED = 10004;
  const CLOSED = 6;
  const TRANS_IN_PROGRESS = 121;
  CONST FIXED = 'Fixed / Done';
  CONST WONT_FIX = 'Won\'t Fix';
  CONST TRANS_CLOSED = 131;
  CONST TRANS_CANCEL = 131;

	public function __construct($config){
    $this->jira_url=$config['jira']['host'];
		$this->jira_user=$config['jira']['user'];
		$this->jira_pwd=$config['jira']['password'];

	}

	public function get_by_key($key){
		try {
			//return $this->getIssue ( $this->token, strtoupper ( $key ) );
			$issue = $this->getIssue($key);
			return $issue['key'];
		}
		catch( Exception $e ) {
			return null;
		}
	}

  public function getTicketUrl($ticket){
  		return $this->jira_url . '/browse/' . $ticket;
  }

  public function getTicketUri($ticket){
  		return substr($ticket, strlen($this->jira_url . '/browse/'));
  }

	public function get_servicelist($service){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERPWD,$this->jira_user.":".$this->jira_pwd);
		curl_setopt($ch, CURLOPT_URL,  $this->jira_url . '/rest/api/latest/' . $service );

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		$res = curl_exec($ch);
		curl_close($ch);

//		if($res->info->http_code == 200)
		$json = json_decode(urldecode($res), true);
		$temp = array();
		foreach($json as $status){
			$temp[$status['id']] = $status['name'];
		}
		return $temp;
	}

	public function search_user($user){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERPWD,$this->jira_user.":".$this->jira_pwd);
		curl_setopt($ch, CURLOPT_URL,  $this->jira_url . '/rest/api/latest/user/search?username=' . $user );

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		$res = curl_exec($ch);
	    if( !$res || $res=="[]" ) {
			curl_close($ch);
			return "";
	    }
		$json = json_decode(urldecode($res), true);
		curl_close($ch);

		return $json[0]['name'];
	}


	public function get_statuses(){
		return $this->get_servicelist("status");
	}


	public function get_resolutions(){
		return $this->get_servicelist("resolution");
	}

	public static function parse_issues($message){
		$issues_keys = array();

		foreach ( explode ( " ", $message ) as $word ) {
			preg_match ( "/\w+-\d+/", $word, $matches );
			if (! empty ( $matches ))
				$issues_keys [] = $matches [0];
		}
		return $issues_keys;
	}

	public function find_issues($message){
		$issues_keys = Jira::parse_issues($message);

		$issues = array ();

		foreach ( $issues_keys as $key ) {
			try {
				$issue = $this->get_by_key ( strtoupper ( $key ) );
				$issues [$issue->key] = $issue;
			} catch ( SoapFault $e ) {
				//The issue doesn't exists
			}
		}
		return $issues;
	}

  public function create_issue($title, $message, $component = '10313', $label = '', $assignee = ''){
			$project = array('id' => '10150');//RLS
			$summary = array('summary'=> $title);
			$description = array('description'=> $message);
			$issuetype = array('id' => '15');//EasyFix
			$components = array(array('name' => $component)); //OLX
			$labels = array($label);
			$env = array('value' => 'LIVE');
			$fields = array('project' => $project, 'summary'=> $title, 'description'=> $message,
				'issuetype'=>$issuetype, 'components'=>$components, 'labels'=>$labels, 'customfield_10141'=>$env);

			$rls = array('fields' => $fields);

			$data_string = json_encode($rls);

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_USERPWD, $this->jira_user.":".$this->jira_pwd);
			curl_setopt($ch, CURLOPT_URL,  $this->jira_url . '/rest/api/latest/issue' );
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    		'Content-Type: application/json',
	    		'Content-Length: ' . strlen($data_string))
			);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		    if( ! $res = curl_exec($ch)) {
		    	var_dump($res);
				error_log($res);
				return null;
		    }

			//$res = curl_exec($ch);
			curl_close($ch);

			$json = json_decode(urldecode($res), true);
			if (!empty($assignee)){
				$this->assignTo($json['key'], $assignee);
			}

			return $json['key'];

		}


    public function assignTo($issue, $user){

			$field_assignee = array('name' => $user );

			$data_string = json_encode($field_assignee);

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_USERPWD, $this->jira_user.":".$this->jira_pwd);
			curl_setopt($ch, CURLOPT_URL,  $this->jira_url . '/rest/api/latest/issue/' . $issue . '/assignee');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    		'Content-Type: application/json',
	    		'Content-Length: ' . strlen($data_string))
			);
			//curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

			$res = curl_exec($ch);
			curl_close($ch);

			$json = json_decode(urldecode($res), true);

			//return $json['key'];

		}


    public function add_label($issue, $label){
			$labels = array();
			$labels['add'] = $label;

			$fields = array('labels'=>array($labels));
			$rls = array('update' => $fields);

			$data_string = json_encode($rls);

			//var_dump($data_string);
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_USERPWD, $this->jira_user.":".$this->jira_pwd);
			curl_setopt($ch, CURLOPT_URL,  $this->jira_url . '/rest/api/latest/issue/' . $issue );
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    		'Content-Type: application/json',
	    		'Content-Length: ' . strlen($data_string))
			);
			//curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

			$res = curl_exec($ch);
			curl_close($ch);

			//$json = json_decode(urldecode($res), true);
			//var_dump($json);

			//return $json['key'];

		}



    public function issue_types(){
        $issue_types = $this->getIssueTypes ( $this->token);
        return $issue_types;
    }

    public function custom_fields(){
        $customs = $this->getCustomFields ( $this->token);
        return $customs;
    }

    public function components($project){
        $components = $this->getComponents ( $this->token, $project);
        return $components;
    }

    public function getComponents($project){
        	return Jira::getComponents_login($project, $this->jira_user, $this->jira_pwd);
		}

    public static function getComponents_login($project, $user, $password){
            $ch = curl_init();
			curl_setopt($ch, CURLOPT_USERPWD,$user.":".$password);
			curl_setopt($ch, CURLOPT_URL,  $this->jira_url . '/rest/api/latest/project/' . $project . '/components' );

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

			$res = curl_exec($ch);
			curl_close($ch);

	//		if($res->info->http_code == 200)
			$components = json_decode(urldecode($res), true);

            return $components;
        }


        public function get_group($group_name){
       			return $this->getGroup ( $this->token, $group_name);
        }

        public function user_belongs_to_group($group_name, $user_name){
                $group = $this->get_group($group_name);
                foreach ($group->users as $user){
                	if ($user->name === $user_name)
                		return true;
                }
                return false;
        }

      public function get_project($project_key){
        	try{
       			return $this->getProjectByKey ( $this->token, $project_key);
			} catch (Exception $e) {
			    echo 'Caught exception: ',  $e->getMessage(), "\n";
			    return null;
			}
       	}

       	public function update_project($project){
       		try {
       			$this->updateProject($this->token, $project);
       		} catch (Exception $e) {
			    echo 'Caught exception: ',  $e->getMessage(), "\n";
			    return null;
       		}
       	}

		public function getIssue($issueId){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERPWD,$this->jira_user.":".$this->jira_pwd);
			curl_setopt($ch, CURLOPT_URL,  $this->jira_url . '/rest/api/latest/issue/' . $issueId );

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

			$res = curl_exec($ch);
			curl_close($ch);

	//		if($res->info->http_code == 200)
			$json = json_decode(urldecode($res), true);

			return $json;
		}


		public function link($masterIssue, $issueLinks){
			foreach ($issueLinks as $link){
				$type = array('name' => 'Block');
				$from = array('key' => $masterIssue);
				$to = array('key' => $link);
				$data = array('type' => $type, 'inwardIssue' => $from, 'outwardIssue'=> $to);
				$data_string = json_encode($data);
				//var_dump($data_string);
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_USERPWD,$this->jira_user.":".$this->jira_pwd);
				curl_setopt($ch, CURLOPT_URL,  $this->jira_url . '/rest/api/latest/issueLink' );
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    		'Content-Type: application/json',
		    		'Content-Length: ' . strlen($data_string))
				);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

				$res = curl_exec($ch);
				curl_close($ch);
			}

		}

		public function transition($issue, $transition, $resolution=''){
			return $this->transition_login($issue, $transition, $resolution, $this->jira_user, $this->jira_pwd);
		}

		public function transition_login($issue, $transition, $resolution, $user, $password){
			$tId = array('id' => $transition);
			if ($resolution!=""){
				$resol = array('resolution' => array('name' => $resolution));
				$data = array('transition' => $tId, 'fields' => $resol);
			}
			else
				$data = array('transition' => $tId);
			$data_string = json_encode($data);
			//var_dump($data_string);
			//echo "\n";
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_USERPWD, $user.":".$password);
			$url =  $this->jira_url . '/rest/api/latest/issue/' . $issue . '/transitions';
			//echo $url . "\n";
			curl_setopt($ch, CURLOPT_URL, $url );
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    		'Content-Type: application/json',
	    		'Content-Length: ' . strlen($data_string))
			);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

			$res = curl_exec($ch);
			curl_close($ch);
			return $res;
		}

}

class RemoteCustomFieldValue{//Not using it, doesn t work
        var $customfieldId;
        var $value;
}


class RemoteComponent{
        var $id;
        var $name;
}
class JiraIssue{
	var $key;
	var $summary;
	var $status;
	var $reporter;
	var $assignee;
	var $deploy_steps;

	public function __construct($_key, $_summary, $_status, $_deploy_steps){
		$this->key = $_key;
		$this->summary = $_summary;
		$this->status = $_status;
		$this->reporter = "";
		$this->assignee = "";
		$this->deploy_steps = $_deploy_steps;
	}

	function isClosed(){
		return $this->status==6;
	}

}
