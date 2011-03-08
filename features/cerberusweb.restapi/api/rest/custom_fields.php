<?php
class ChRest_CustomFields extends Extension_RestController implements IExtensionRestController {
	function getAction($stack) {
		@$context = array_shift($stack);

		switch($context) {
			case 'tickets':
			case 'ticket':
				$context = CerberusContexts::CONTEXT_TICKET;
				break;
			case 'addresses':
			case 'address':
				$context = CerberusContexts::CONTEXT_ADDRESS;
				break;
			case 'tasks':
			case 'task':
				$context = CerberusContexts::CONTEXT_TASK;
				break;
			case 'orgs':
			case 'org':
				$context = CerberusContexts::CONTEXT_ORG;
				break;
			case 'feedback':
				$context = CerberusContexts::CONTEXT_FEEDBACK;
				break;
			case 'worker':
				$context = CerberusContexts::CONTEXT_WORKER;
				break;
			default:
				$this->error(self::ERRNO_CUSTOM, sprintf('%s is not a valid custom field object!', $context));
				exit;			
			
		}
		
		$fields = array();
		if(null != ($context_fields = DAO_CustomField::getByContext($context)) && !empty($context_fields))
			foreach($context_fields as $field)
				$fields['custom_' . $field->id] = array($field->name, $field->type, $field->options);
			
		
		$this->success($fields);
		
	}
	
	function putAction($stack) {
		@$action = array_shift($stack);
		
		// Looking up a single ID?
		if(is_numeric($action)) {
			$this->putId(intval($action));
			
		} else { // actions
			switch($action) {
				default:
					$this->error(self::ERRNO_NOT_IMPLEMENTED);
					break;
			}
		}
	}
	
	function postAction($stack) {
		@$action = array_shift($stack);
		
		switch($action) {
			case 'create':
				$this->postCreate();
				break;
			case 'search':
				$this->postSearch();
				break;
		}
		
		$this->error(self::ERRNO_NOT_IMPLEMENTED);
	}
	
	function deleteAction($stack) {
		$this->error(self::ERRNO_NOT_IMPLEMENTED);
	}
	
	private function getId($id) {
		$worker = $this->getActiveWorker();
		
//		// ACL
//		if(!$worker->hasPriv('core.addybook'))
//			$this->error(self::ERRNO_ACL);

		$container = $this->search(array(
			array('id', '=', $id),
		));
		
		if(is_array($container) && isset($container['results']) && isset($container['results'][$id]))
			$this->success($container['results'][$id]);

		// Error
		$this->error(self::ERRNO_CUSTOM, sprintf("Invalid comment id '%d'", $id));
	}

	function translateToken($token, $type='dao') {
		$tokens = array();
		
		if('dao'==$type) {
			$tokens = array(
				'context' => DAO_Comment::CONTEXT,
				'context_id' => DAO_Comment::CONTEXT_ID,
				'address_id' => DAO_Comment::ADDRESS_ID,
				'comment' => DAO_Comment::COMMENT,
				'created' => DAO_Comment::CREATED,
			);
		} else {
			$tokens = array(
				'id' => SearchFields_Comment::ID,
				'context' => SearchFields_Comment::CONTEXT,
				'context_id' => SearchFields_Comment::CONTEXT_ID,
				'address_id' => SearchFields_Comment::ADDRESS_ID,
				'comment' => SearchFields_Comment::COMMENT,
			);
		}
		
		if(isset($tokens[$token]))
			return $tokens[$token];
		
		return NULL;
	}	
	
	function getContext($id) {
		$labels = array();
		$values = array();
		$context = CerberusContexts::getContext(CerberusContexts::CONTEXT_ADDRESS, $id, $labels, $values, null, true);

		return $values;
	}
	
	function search($filters=array(), $sortToken='email', $sortAsc=1, $page=1, $limit=10) {
		$worker = $this->getActiveWorker();

		$params = $this->_handleSearchBuildParams($filters);
		
		// (ACL) Add worker group privs
		if(!$worker->is_superuser) {
			$memberships = $worker->getMemberships();
			$params['tmp_worker_memberships'] = new DevblocksSearchCriteria(
				SearchFields_Ticket::TICKET_TEAM_ID,
				'in',
				(!empty($memberships) ? array_keys($memberships) : array(0))
			);
		}
		
		// Sort
		$sortBy = $this->translateToken($sortToken, 'search');
		$sortAsc = !empty($sortAsc) ? true : false;
		
		// Search
		list($results, $total) = DAO_Comment::search(
			array(),
			$params,
			$limit,
			max(0,$page-1),
			$sortBy,
			$sortAsc,
			true
		);
		
		$objects = array();
		
		foreach($results as $id => $result) {
			$values = $this->getContext($id);
			$objects[$id] = $values;
		}
		
		$container = array(
			'total' => $total,
			'count' => count($objects),
			'page' => $page,
			'results' => $objects,
		);
		
		return $container;		
	}
	
	function putId($id) {
		$worker = $this->getActiveWorker();
		
		// ACL
		if(!$worker->hasPriv('core.addybook.addy.actions.update'))
			$this->error(self::ERRNO_ACL);
		
		// Validate the ID
		if(null == DAO_Address::get($id))
			$this->error(self::ERRNO_CUSTOM, sprintf("Invalid address ID '%d'", $id));
			
		$putfields = array(
			'first_name' => 'string',
			'is_banned' => 'bit',
			'last_name' => 'string',
			'org_id' => 'integer',
		);

		$fields = array();

		foreach($putfields as $putfield => $type) {
			if(!isset($_POST[$putfield]))
				continue;
			
			@$value = DevblocksPlatform::importGPC($_POST[$putfield], 'string', '');
			
			if(null == ($field = self::translateToken($putfield, 'dao'))) {
				$this->error(self::ERRNO_CUSTOM, sprintf("'%s' is not a valid field.", $putfield));
			}
			
			// Sanitize
			$value = DevblocksPlatform::importVar($value, $type);

			// Overrides
			switch($field) {
			}
			
			$fields[$field] = $value;
		}
		
		// Handle custom fields
		$customfields = $this->_handleCustomFields($_POST);
		if(is_array($customfields))
			DAO_CustomFieldValue::formatAndSetFieldValues(CerberusContexts::CONTEXT_ADDRESS, $id, $customfields, true, true, true);
		
		// Check required fields
//		$reqfields = array(DAO_Address::EMAIL);
//		$this->_handleRequiredFields($reqfields, $fields);

		// Update
		DAO_Address::update($id, $fields);
		$this->getId($id);
	}
	
	function postCreate() {
		$worker = $this->getActiveWorker();
		
		// ACL
//		if(!$worker->hasPriv('core.addybook.addy.actions.update'))
//			$this->error(self::ERRNO_ACL);
		
		$postfields = array(
			'context' => 'string',
			'context_id' => 'integer',
			'address' => 'string',
			'address_id' => 'integer',
			'created' => 'integer',
			'comment' => 'string',
		);

		$fields = array();
		
		foreach($postfields as $postfield => $type) {
			if(!isset($_POST[$postfield]))
				continue;
				
			@$value = DevblocksPlatform::importGPC($_POST[$postfield], 'string', '');
			
			switch($postfield) {
				case 'address':
					if(null != ($lookup = DAO_Address::lookupAddress($value, true))) {
						unset($postfields['address']);
						$postfield = 'address_id';
						$value = $lookup->id;
					}
					break;
				case 'context':
					switch($_POST[$postfield]) {
						case CerberusContexts::CONTEXT_TICKET:
							if(!$worker->hasPriv('core.display.actions.comment')) {
								$this->error(self::ERRNO_ACL);
							}
							break;
					break;
				}
			}
			
			if(null == ($field = self::translateToken($postfield, 'dao'))) {
				$this->error(self::ERRNO_CUSTOM, sprintf("'%s' is not a valid field.", $postfield));
			}

			// Sanitize
			$value = DevblocksPlatform::importVar($value, $type);
			
			// Overrides
			switch($field) {
			}
			
			$fields[$field] = $value;
		}
		
		// Defaults
		if(!isset($fields[DAO_Comment::CREATED]))
			$fields[DAO_Comment::CREATED] = time();
		
		// Check required fields
//		$reqfields = array(DAO_Address::EMAIL);
		$reqfields = array(DAO_Comment::CONTEXT, DAO_Comment::CONTEXT_ID, DAO_Comment::ADDRESS_ID, DAO_Comment::COMMENT, DAO_Comment::CREATED);
		$this->_handleRequiredFields($reqfields, $fields);
		
		// Create
		if(false != ($id = DAO_Comment::create($fields))) {
			$this->getId($id);
		}
	}
	
	function postSearch() {
		$worker = $this->getActiveWorker();
		
		// ACL
		if(!$worker->hasPriv('core.addybook'))
			$this->error(self::ERRNO_ACL);

		$container = $this->_handlePostSearch();
		
		$this->success($container);
	}
};