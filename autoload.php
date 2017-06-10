<?php
define("PATH_THIRDPARTY", $_SERVER['DOCUMENT_ROOT']. '/core/app/library/thirdparty/');
define("PATH_SEP", "/");

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Mysql.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BaseVariable.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Variable.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/StepVariable.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BaseComments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Comments.php';

/* * *********************** Documents ******************************************** */
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/StepDocument.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/StepDocuments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/BaseProcessFiles.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/ProcessFiles.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/FileUpload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/BaseDocumentVersion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/BaseInputDocument.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/DocumentVersion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/Attachments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/InputDocuments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/InputDocument.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/OutputDocuments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/BaseOutputDocument.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/OutputDocument.php';


/************************* Fields **************************************************/
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/Field.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/RequiredField.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/StepField.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/FieldFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/FieldOptions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/FieldValidator.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/BaseFieldCondition.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/DatabaseOptions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/FieldConditions.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/Form.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/FormBuilder.php';


/* * ************** Workflow Classes ********************************************* */
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/Validator.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Process/BaseProcess.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Workflow.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/WorkflowStep.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/BaseWorkflowCollection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/WorkflowCollection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/WorkflowCollectionFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/BaseGateway.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Gateway.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/BaseTrigger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Trigger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/StepGateway.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/StepTrigger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Permissions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/ObjectPermissions.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/StepPermissions.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/Lists.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Save.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Elements.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/Cases.php';

/* * ************** Notification Classes *********************** */
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Notifications/BaseNotifications.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Notifications/Notifications.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Notifications/SendNotification.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/NotificationsFactory.php';
/* * ********************* BPMN Classes ***************************************************** */
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/BPMN.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/BPMNWorkflow.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/Task.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/Participant.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/Message.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/Flow.php';;
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/Diagram.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/Conditions.php';

/* * ***********************Process *************************************************** */;
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Process/BaseProcessRoute.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Process/ProcessRoute.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/Process.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/ProcessSupervisor.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Process/BaseProcessUser.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Process/ProcessUser.php';



/* * ******************* Users ******************************************* */
//require_once $_SERVER['DOCUMENT_ROOT'].'/core/app/library/Users.php'
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/Password.php';;
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/BaseUser.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/BasePermission.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/Permission.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/Users.php';;
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/UsersFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/RoleUser.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/Role.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/Users.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/Department.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/BaseDepartment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/Departments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/BaseRole.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/BaseRolePermissions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/RolePermissions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/Roles.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/Login.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/BaseTeam.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/Teams.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/GroupUser.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/Team.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/RolePermissions.php';


require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BusinessModel/RolePermission.php';

//require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/RolePermissions.php';

/* * *************************88 Dashboard *************************************** */
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Dashboard/Dashboard.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Dashboard/DashboardUser.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Dashboard/DashboardInstance.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Dashboard/DashboardBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Dashboard/DashboardFactory.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Audit/BaseAudit.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Audit/Audit.php';

