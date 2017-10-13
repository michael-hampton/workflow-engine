<?php

define ("HOME_DIR", "C:/xampp/htdocs/");
define ("LOG_FILE", "C:/xampp/htdocs/core/app/logs/easyflow.log");
define ("PATH_THIRDPARTY", HOME_DIR . '/core/app/library/thirdparty/');
define ("PATH_DATA_MAILTEMPLATES", HOME_DIR . 'core/public/templates/');
define ("DIAGRAMS", HOME_DIR . "core/public/BPMNdata/");
define ("PATH_DATA_PUBLIC", HOME_DIR . "FormBuilder/public/");
define ("PATH_SEP", "/");
define ("UPLOADS_DIR", PATH_DATA_PUBLIC . "uploads/");
define ("OUTPUT_DOCUMENTS", UPLOADS_DIR . "OutputDocuments/");
define ("PATH_IMAGES_ENVIRONMENT_USERS", HOME_DIR . PATH_DATA_PUBLIC . "img/users");
define ("WEB_ENTRY_DIR", HOME_DIR . "core/public/webentry/");
define ("WEB_ENTRY_TEMPLATES", WEB_ENTRY_DIR . "template.phtml");
define ("HOST", $_SERVER['HTTP_HOST']);
define ('SERVER_NAME', $_SERVER["SERVER_NAME"]);

require_once HOME_DIR . "/core/app/config/config.php";
require_once HOME_DIR . '/core/app/library/Persistent.php';
require_once HOME_DIR . '/core/app/library/Log.php';
require_once HOME_DIR . '/core/app/library/BaseConfiguration.php';
require_once HOME_DIR . '/core/app/library/Configuration.php';
require_once HOME_DIR . '/core/app/library/class.configuration.php';
require_once HOME_DIR . '/core/app/library/netClass.php';

require_once HOME_DIR . '/core/app/library/BusinessModel/Validator.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/Download.php';
require_once HOME_DIR . '/core/app/library/config.php';
require_once HOME_DIR . '/core/app/library/Mysql.php';
require_once HOME_DIR . '/core/app/library/BaseVariable.php';
require_once HOME_DIR . '/core/app/library/Variable.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/StepVariable.php';
require_once HOME_DIR . '/core/app/library/BaseComments.php';
require_once HOME_DIR . '/core/app/library/Comments.php';

require_once HOME_DIR . '/core/app/library/BaseStep.php';
require_once HOME_DIR . '/core/app/library/Step.php';

require_once HOME_DIR . '/core/app/library/BusinessModel/Step.php';

/* * *********************** Events ******************************************** */
require_once HOME_DIR . '/core/app/library/Event/BaseMessageApplication.php';
require_once HOME_DIR . '/core/app/library/Event/BaseMessageEventRelation.php';
require_once HOME_DIR . '/core/app/library/Event/MessageApplication.php';
require_once HOME_DIR . '/core/app/library/Event/MessageEventRelation.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/MessageEventRelation.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/MessageApplication.php';
require_once HOME_DIR . '/core/app/library/Event/BaseAppEvent.php';
require_once HOME_DIR . '/core/app/library/Event/AppEvent.php';
require_once HOME_DIR . '/core/app/library/Event/BaseEvent.php';
require_once HOME_DIR . '/core/app/library/Event/EventModel.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/Event.php';
require_once HOME_DIR . '/core/app/library/Event/BaseMessageType.php';
require_once HOME_DIR . '/core/app/library/Event/MessageType.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/MessageType.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/TimerEvent.php';
require_once HOME_DIR . '/core/app/library/Event/BaseTimerEvent.php';
require_once HOME_DIR . '/core/app/library/Event/TimerEvent.php';
require_once HOME_DIR . '/core/app/library/Event/BaseMessageDefinition.php';
require_once HOME_DIR . '/core/app/library/Event/MessageDefinition.php';

require_once HOME_DIR . '/core/app/library/BusinessModel/MessageEventDefinition.php';
require_once HOME_DIR . '/core/app/library/Event/BaseWebEntry.php';
require_once HOME_DIR . '/core/app/library/Event/WebEntry.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/WebEntry.php';
require_once HOME_DIR . '/core/app/library/Event/BaseWebEntryEvent.php';
require_once HOME_DIR . '/core/app/library/Event/WebEntryEvent.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/WebEntryEvent.php';

require_once HOME_DIR . '/core/app/library/Calendar/BaseCalendarDefinition.php';
require_once HOME_DIR . '/core/app/library/Calendar/CalendarDefinition.php';
require_once HOME_DIR . '/core/app/library/CalendarFunctions.php';

require_once HOME_DIR . '/core/app/library/WorkflowClasses/BaseAppThread.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/AppThread.php';

require_once HOME_DIR . '/core/app/library/WorkflowClasses/BaseAppDelegation.php';
require_once HOME_DIR . '/core/app/library/Event/AppDelegation.php';

require_once HOME_DIR . '/core/app/library/WorkflowClasses/BaseAppDelay.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/AppDelay.php';

require_once HOME_DIR . '/core/app/library/Tables/BaseReportVar.php';
require_once HOME_DIR . '/core/app/library/Tables/ReportVar.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/BaseCaseConsolidatedCore.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/caseConsolidatedCore.php';
require_once HOME_DIR . '/core/app/library/consolidatedCases.php';

require_once HOME_DIR . '/core/app/library/Event/BaseMessageTypeVariable.php';
require_once HOME_DIR . '/core/app/library/Event/MessageTypeVariable.php';


/* * *********************** Documents ******************************************** */
//require_once HOME_DIR . '/core/app/library/Documents/StepDocument.php';
//require_once HOME_DIR . '/core/app/library/BusinessModel/StepDocument.php';

require_once HOME_DIR . '/core/app/library/Documents/BaseAppFolder.php';
require_once HOME_DIR . '/core/app/library/Documents/AppFolder.php';

require_once HOME_DIR . '/core/app/library/Documents/BaseProcessFile.php';
require_once HOME_DIR . '/core/app/library/Documents/ProcessFile.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/FileUpload.php';
require_once HOME_DIR . '/core/app/library/Documents/BaseDocumentVersion.php';
require_once HOME_DIR . '/core/app/library/Documents/BaseInputDocument.php';
require_once HOME_DIR . '/core/app/library/Documents/DocumentVersion.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/Attachment.php';
require_once HOME_DIR . '/core/app/library/Documents/InputDocument.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/InputDocument.php';

require_once HOME_DIR . '/core/app/library/BusinessModel/OutputDocument.php';
require_once HOME_DIR . '/core/app/library/Documents/BaseOutputDocument.php';
require_once HOME_DIR . '/core/app/library/Documents/OutputDocument.php';


/* * *********************** Fields ************************************************* */
require_once HOME_DIR . '/core/app/library/Fields/Field.php';
require_once HOME_DIR . '/core/app/library/Fields/RequiredField.php';
require_once HOME_DIR . '/core/app/library/Fields/StepField.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/FieldFactory.php';
require_once HOME_DIR . '/core/app/library/Fields/FieldOptions.php';
require_once HOME_DIR . '/core/app/library/Fields/FieldValidator.php';

require_once HOME_DIR . '/core/app/library/Fields/BaseFieldCondition.php';
require_once HOME_DIR . '/core/app/library/Fields/DatabaseOptions.php';
require_once HOME_DIR . '/core/app/library/Fields/FieldConditions.php';

require_once HOME_DIR . '/core/app/library/BusinessModel/Form.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/FormBuilder.php';

require_once HOME_DIR . '/core/app/library/Fields/BaseForm.php';
require_once HOME_DIR . '/core/app/library/Fields/Form.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/FormAdmin.php';


/* * ************** Workflow Classes ********************************************* */

require_once HOME_DIR . '/core/app/library/Process/BaseProcess.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/Workflow.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/WorkflowStep.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/BaseWorkflowCollection.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/WorkflowCollection.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/WorkflowCollectionFactory.php';
require_once HOME_DIR . '/core/app/library/BPMN/BaseGateway.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/Gateway.php';

require_once HOME_DIR . '/core/app/library/WorkflowClasses/BaseTrigger.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/Trigger.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/StepGateway.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/StepTrigger.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/Permissions.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/ObjectPermissions.php';

require_once HOME_DIR . '/core/app/library/WorkflowClasses/BaseProcessPermission.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/ProcessPermission.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/ProcessPermission.php';

require_once HOME_DIR . '/core/app/library/BusinessModel/StepPermission.php';

require_once HOME_DIR . '/core/app/library/BusinessModel/Lists.php';

require_once HOME_DIR . '/core/app/library/WorkflowClasses/Save.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/BaseAppSequence.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/AppSequence.php';
require_once HOME_DIR . '/core/app/library/WorkflowClasses/Elements.php';
require_once HOME_DIR . '/core/app/library/pluginRegistry.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/Cases.php';

require_once HOME_DIR . '/core/app/library/TeamFunctions.php';

/* * ************** Notification Classes *********************** */
require_once HOME_DIR . '/core/app/library/Notifications/BaseNotification.php';
require_once HOME_DIR . '/core/app/library/Notifications/Notification.php';

require_once HOME_DIR . '/core/app/library/Notifications/BaseAbeRequest.php';
require_once HOME_DIR . '/core/app/library/Notifications/BaseAbeResponse.php';
require_once HOME_DIR . '/core/app/library/Notifications/AbeRequest.php';
require_once HOME_DIR . '/core/app/library/Notifications/AbeResponse.php';

require_once HOME_DIR . '/core/app/library/EmailActions.php';

require_once HOME_DIR . '/core/app/library/Notifications/SendNotification.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/NotificationsFactory.php';
//require_once HOME_DIR . '/core/app/library/BusinessModel/EmailTemplate.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/Notification.php';

require_once HOME_DIR . '/core/app/library/BusinessModel/FilesManager.php';

require_once HOME_DIR . '/core/app/library/Notifications/BaseAppMessage.php';
require_once HOME_DIR . '/core/app/library/Notifications/AppMessage.php';

require_once HOME_DIR . '/core/app/library/EmailFunctions.php';

require_once HOME_DIR . '/core/app/library/BusinessModel/EmailServer.php';
require_once HOME_DIR . '/core/app/library/Notifications/BaseEmailServer.php';
require_once HOME_DIR . '/core/app/library/Notifications/EmailServer.php';

/* * ********************* BPMN Classes ***************************************************** */
require_once HOME_DIR . '/core/app/library/BPMN/BPMN.php';
require_once HOME_DIR . '/core/app/library/BPMN/BPMNWorkflow.php';
require_once HOME_DIR . '/core/app/library/BPMN/BaseTask.php';
require_once HOME_DIR . '/core/app/library/BPMN/Task.php';
require_once HOME_DIR . '/core/app/library/Process/BaseTaskUser.php';
require_once HOME_DIR . '/core/app/library/Process/TaskUser.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/Task.php';
require_once HOME_DIR . '/core/app/library/BPMN/Participant.php';
require_once HOME_DIR . '/core/app/library/BPMN/Message.php';
require_once HOME_DIR . '/core/app/library/BPMN/Flow.php';

require_once HOME_DIR . '/core/app/library/ScriptFunctions.php';
require_once HOME_DIR . '/core/app/library/Event/BaseScriptTask.php';
require_once HOME_DIR . '/core/app/library/Event/ScriptTask.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/ScriptTask.php';

require_once HOME_DIR . '/core/app/library/BPMN/Diagram.php';
require_once HOME_DIR . '/core/app/library/BPMN/Conditions.php';

/* * ***********************Process *************************************************** */;
require_once HOME_DIR . '/core/app/library/Process/BaseProcessRoute.php';
require_once HOME_DIR . '/core/app/library/Process/ProcessRoute.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/Process.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/ProcessSupervisor.php';
require_once HOME_DIR . '/core/app/library/Process/BaseProcessUser.php';
require_once HOME_DIR . '/core/app/library/Process/ProcessUser.php';

/* * ******************* Users ******************************************* */
//require_once HOME_DIR.'/core/app/library/Users.php'
require_once HOME_DIR . '/core/app/library/BusinessModel/Password.php';

require_once HOME_DIR . '/core/app/library/UserClasses/BaseUser.php';
require_once HOME_DIR . '/core/app/library/UserClasses/BasePermission.php';
require_once HOME_DIR . '/core/app/library/UserClasses/Permission.php';
require_once HOME_DIR . '/core/app/library/UserClasses/Users.php';
require_once HOME_DIR . '/core/app/library/UserClasses/BaseUserProperties.php';
require_once HOME_DIR . '/core/app/library/UserClasses/UserProperties.php';

require_once HOME_DIR . '/core/app/library/BusinessModel/UsersFactory.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/RoleUser.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/Role.php';
require_once HOME_DIR . '/core/app/library/UserClasses/Users.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/Department.php';
require_once HOME_DIR . '/core/app/library/UserClasses/BaseDepartment.php';
require_once HOME_DIR . '/core/app/library/UserClasses/Department.php';
require_once HOME_DIR . '/core/app/library/UserClasses/BaseRole.php';
require_once HOME_DIR . '/core/app/library/UserClasses/BaseRolePermission.php';
require_once HOME_DIR . '/core/app/library/UserClasses/RolePermissions.php';
require_once HOME_DIR . '/core/app/library/UserClasses/Role.php';
require_once HOME_DIR . '/core/app/library/UserClasses/Login.php';
require_once HOME_DIR . '/core/app/library/UserClasses/BaseTeam.php';
require_once HOME_DIR . '/core/app/library/UserClasses/Team.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/GroupUser.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/Team.php';
require_once HOME_DIR . '/core/app/library/UserClasses/RolePermissions.php';


require_once HOME_DIR . '/core/app/library/BusinessModel/RolePermission.php';

//require_once HOME_DIR . '/core/app/library/UserClasses/RolePermissions.php';

/* * *************************88 Dashboard *************************************** */
require_once HOME_DIR . '/core/app/library/Dashboard/Dashboard.php';
require_once HOME_DIR . '/core/app/library/Dashboard/DashboardUser.php';
require_once HOME_DIR . '/core/app/library/Dashboard/DashboardInstance.php';
require_once HOME_DIR . '/core/app/library/Dashboard/DashboardBuilder.php';
require_once HOME_DIR . '/core/app/library/Dashboard/DashboardFactory.php';

require_once HOME_DIR . '/core/app/library/Audit/BaseAudit.php';
require_once HOME_DIR . '/core/app/library/Audit/Audit.php';

/* * ************** Calendar *********************** */
require_once HOME_DIR . '/core/app/library/Calendar/BaseCalendarBusinessHours.php';
require_once HOME_DIR . '/core/app/library/Calendar/CalendarBusinessHours.php';

require_once HOME_DIR . '/core/app/library/Calendar/BaseCalendarHolidays.php';
require_once HOME_DIR . '/core/app/library/Calendar/CalendarHolidays.php';

require_once HOME_DIR . '/core/app/library/Calendar/BaseCalendarAssignment.php';
require_once HOME_DIR . '/core/app/library/Calendar/CalendarAssignment.php';


require_once HOME_DIR . '/core/app/library/BusinessModel/Calendar.php';


require_once HOME_DIR . 'core/app/library/Step/InputDocument.php';
require_once HOME_DIR . 'core/app/library/MessageType/Variable.php';

require_once HOME_DIR . 'core/app/library/Tables/SaveReport.php';
require_once HOME_DIR . 'core/app/library/reportTableCSV.php';
require_once HOME_DIR . 'core/app/library/Tables/pmTable.php';
require_once HOME_DIR . 'core/app/library/Tables/BaseAdditionalTable.php';
require_once HOME_DIR . 'core/app/library/Tables/AdditionalTables.php';
require_once HOME_DIR . 'core/app/library/Tables/BaseReportField.php';
require_once HOME_DIR . 'core/app/library/Tables/ReportField.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/ReportTable.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/Table.php';

require_once HOME_DIR . '/core/app/library/CaseTracker/BaseCaseTrackerObject.php';
require_once HOME_DIR . '/core/app/library/CaseTracker/BaseCaseTracker.php';
require_once HOME_DIR . '/core/app/library/CaseTracker/CaseTrackerObject.php';
require_once HOME_DIR . '/core/app/library/CaseTracker/CaseTracker.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/CaseTracker.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/CaseTrackerObject.php';

require_once HOME_DIR . '/core/app/library/BusinessModel/BaseAppCacheView.php';
require_once HOME_DIR . '/core/app/library/BusinessModel/AppCacheView.php';

require_once HOME_DIR . '/core/app/library/Iso.php';
require_once HOME_DIR . '/core/app/library/BaseLoginLog.php';
require_once HOME_DIR . '/core/app/library/LoginLog.php';
