<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Mysql.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Variable.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/StepVariable.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BaseComments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Comments.php';

/* * *********************** Documents ******************************************** */
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/ProcessFiles.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/FileUpload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/BaseDocumentVersion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/DocumentVersion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/Attachments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/InputDocument.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Documents/StepDocument.php';

/************************* Fields **************************************************/
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/Field.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/RequiredField.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/StepField.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/FieldFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/FieldOptions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/FieldValidator.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/BaseFieldCondition.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/DatabaseOptions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/FieldConditions.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/Form.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Fields/FormBuilder.php';


/* * ************** Workflow Classes ********************************************* */
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Validator.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Workflow.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/WorkflowStep.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/WorkflowCollectionFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/WorkflowCollection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/BaseGateway.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Gateway.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Trigger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/StepTrigger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/StepPermissions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Permissions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Lists.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Save.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Elements.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/WorkflowClasses/Cases.php';

/* * ************** Notification Classes *********************** */
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Notifications/Notifications.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Notifications/SendNotification.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Notifications/NotificationsFactory.php';
/* * ********************* BPMN Classes ***************************************************** */
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/BPMN.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/BPMNWorkflow.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/Task.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/Participant.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/Message.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/Flow.php';;
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/Diagram.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/BPMN/Conditions.php';

/* * ***********************Process *************************************************** */
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Process/BaseProcess.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Process/ProcessRoute.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Process/Process.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Process/ProcessSupervisor.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Process/ProcessUser.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Process/BaseProcessUser.php';


/* * ******************* Users ******************************************* */
//require_once $_SERVER['DOCUMENT_ROOT'].'/core/app/library/Users.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/UsersFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/RolesFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/Users.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/Departments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/Roles.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/Login.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/Teams.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/RolePermissions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/UserClasses/TeamFactory.php';

/* * *************************88 Dashboard *************************************** */
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Dashboard/Dashboard.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Dashboard/DashboardUser.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Dashboard/DashboardInstance.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Dashboard/DashboardBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/app/library/Dashboard/DashboardFactory.php';

