<?php
if (!defined('STATUSNET') && !defined('LACONICA')) {
    exit(1);
}

class VideoSetPlayingForm extends Form {
	public $v;
	
	public function __construct($out, $v) {
		parent::__construct($out);
		$this->v = $v;
	}
	
	function id() {
		return 'videosync_setplaying-form-' . $this->v->id;
	}
	
	function formClass() {
		return 'videosync_setplaying-form ajax';
	}
	
    function action() {
        return common_local_url('switchvideo');
    }
	/*
    function formLegend() {
        $this->out->element('legend', null, _('Update video #' . $this->v->id));
    }*/
    function sessionToken()
    {
        $this->out->hidden('token-videoswitch',
                           common_session_token());
    }

    function formData() {
        $this->out->hidden('videoswitch-submit',
                           $this->v->tag,
                           'videoswitch-submit');
    }
   
    function formActions() {
		$this->out->element('input',
			array(
				'type' => 'submit',
				'name' => 'submit',
				'class' => 'submit',
				'title' => _('Play this video now'),
				'value' => _('Play'),
			));
    }
}