<?php

namespace Services;

use Library\HttpRequest;

class ThirdParty {

    private $_preDeployUrl;
    private $_postDeployUrl;
    private $_permissionsUrl;
    private $_adminTeamId;
    private $_pocsTeamId;
    private $_log;
    private $_ticketer;
    const DEPLOY_SUCCESS = "success";
    const DEPLOY_FAILED = "failure";
    const DEPLOY_CANCEL = "cancel";
    const NOT_AVAILABLE = "none";

    const DEPLOY_ACTION = "deploy";
    const ROLLBACK_ACTION = "rollback";

    public function __construct($config,
                                $log, $ticketer=null) {
        if (isset($config['thirdparty']['pre-deploy']))
            $this->_preDeployUrl = $config['thirdparty']['pre-deploy'];
        if (isset($config['thirdparty']['post-deploy']))
            $this->_postDeployUrl = $config['thirdparty']['post-deploy'];
        if (isset($config['thirdparty']['member-permissions']))
            $this->_permissionsUrl = $config['thirdparty']['member-permissions'];
        if (isset($ticketer))
            $this->_ticketer = $ticketer;

        $this->_adminTeamId = $config['teams']['admin'];
        $this->_pocsTeamId = $config['teams']['pocs'];
        $this->_log = $log;
        $this->_log->addInfo("ThirdParty instance created");
    }

    public function preDeploy($job, $action = 'deploy')
    {
        if (isset($this->_preDeployUrl))
            return $this->_externalCall($job, $this->_preDeployUrl, $action);
        elseif (isset($this->_ticketer)){
          $tkt_user = $this->_ticketer->search_user($job->getUser());
          $title = $job->getTargetModule() . "::" . "Deploy " . $job->getTargetVersion();
          $description = "Deploy " . $job->getTargetModule() . " " . $job->getTargetVersion();

          $ticket = $this->_ticketer->create_issue($title, $description,
                          strtoupper($job->getTargetModule()), '', $tkt_user);

          $this->_ticketer->transition($ticket, Jira::TRANS_IN_PROGRESS);

          if ($action==ThirdParty::ROLLBACK_ACTION){//When Rollback, mark and link with new.
            $ticket_old=$this->_ticketer->getTicketUri($job->getTicket());
            $this->_ticketer->add_label($ticket_old, "Rollbacked");
            $this->_ticketer->link($ticket_old, array($ticket));
          }//Normally creates a new Ticket

          return $this->_ticketer->getTicketUrl($ticket);
        }

        return NOT_AVAILABLE;
    }

    public function postDeploy($job, $action = ThirdParty::DEPLOY_SUCCESS)
    {
        $ticket=$this->_ticketer->getTicketUri($job->getTicket());
        if (isset($this->_postDeployUrl))
            return $this->_externalCall($job, $this->_postDeployUrl, $action);
        elseif (isset($this->_ticketer)){
          if ($action==ThirdParty::DEPLOY_SUCCESS)
            return $this->_ticketer->transition($ticket, Jira::TRANS_CLOSED, Jira::FIXED);
          elseif ($action==ThirdParty::DEPLOY_FAILED)
            return $this->_ticketer->transition($ticket, Jira::TRANS_CLOSED, Jira::WONT_FIX);
          elseif ($action==ThirdParty::DEPLOY_CANCEL)
            return $this->_ticketer->transition($ticket, Jira::TRANS_CLOSED, Jira::WONT_FIX);//label CANCELED?
          else
            return NOT_AVAILABLE;
        }
    }

    public function getMemberPermissions($username)
    {
        if (isset($this->_permissionsUrl)){
            $url = $this->_permissionsUrl . $username;
            $perms = json_decode(@file_get_contents($url), true);
        }
        else
            $perms = array('teams' => "*", 'repositories' => "*");

        return $perms;
    }

    public function canMemberGoLive($permissions, $repository)
    {
/*
        if (isset($permissions["teams"]) &&
                isset($permissions["repositories"])) {
            if (in_array($this->_adminTeamId, $permissions["teams"]) ||
                    (in_array($repository, $permissions["repositories"]) &&
                    in_array($this->_pocsTeamId, $permissions["teams"]))) {
                return true;
            }
        }
        return false;
*/
      return !empty($permissions);
    }

    private function _externalCall($job, $external_url, $action) {
        $params = array(
            'repo' => $job->getTargetModule(),
            'version' => $job->getTargetVersion(),
            'ticket' => urlencode($job->getTicket()),
            'user' => $job->getUser(),
            'action' => $action
        );

        $url = $external_url . '?' . http_build_query($params);
        $response = json_decode(file_get_contents($url));
        $response->ticket = urldecode($response->ticket);
        return $response->ticket;
    }

}
