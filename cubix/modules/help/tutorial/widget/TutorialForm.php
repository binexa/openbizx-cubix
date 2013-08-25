<?php 
class TutorialForm extends EasyForm
{
	public function closeTutorial()
	{
		$tutorailId = $this->recordId;
		$rec= $this->readInputs();
		if($rec['chk_show_on_next'])
		{
			$showOnNextLogin = 1;
		}
		else
		{
			$showOnNextLogin = 0;
		}
		BizSystem::getService("help.lib.TutorialService")->SetTutorialShown($tutorailId,$showOnNextLogin);
		$this->close();
	}
	
}
?>