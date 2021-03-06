<?php
class PageSection_SetupPortals extends Extension_PageSection {
	function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$visit = CerberusApplication::getVisit();
		
		$visit->set(ChConfigurationPage::ID, 'portals');
		
	    // View
		
		$defaults = new C4_AbstractViewModel();
		$defaults->id = 'portals_cfg';
		$defaults->class_name = 'View_CommunityPortal';
		
		$view = C4_AbstractViewLoader::getView($defaults->id, $defaults);
		$tpl->assign('view', $view);
	    
		$tpl->display('devblocks:cerberusweb.core::configuration/section/portals/index.tpl');		
	}
	
	function showAddPortalPeekAction() {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$tool_manifests = DevblocksPlatform::getExtensions('usermeet.tool', false);
		$tpl->assign('tool_manifests', $tool_manifests);
		
		$tpl->display('devblocks:cerberusweb.core::configuration/section/portals/add.tpl');
	}
	
	function saveAddPortalPeekAction() {
		@$name = DevblocksPlatform::importGPC($_POST['name'],'string', '');
		@$extension_id = DevblocksPlatform::importGPC($_POST['extension_id'],'string', '');
		
		$portal_code = DAO_CommunityTool::generateUniqueCode();
		
		// Create portal
		$fields = array(
			DAO_CommunityTool::NAME => $name,
			DAO_CommunityTool::EXTENSION_ID => $extension_id,
			DAO_CommunityTool::CODE => $portal_code,
		);
		$portal_id = DAO_CommunityTool::create($fields);
		
		// Redirect to the display page
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('community',$portal_code)));
	}	
}