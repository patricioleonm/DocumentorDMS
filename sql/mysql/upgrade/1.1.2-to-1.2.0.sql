CREATE TABLE archive_restoration_request (
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
request_user_id INTEGER NOT NULL,
admin_user_id INTEGER NOT NULL,
datetime DATETIME NOT NULL
)  TYPE = InnoDB;
ALTER TABLE archiving_settings ADD COLUMN archiving_type_id INTEGER NOT NULL;
DROP TABLE archiving_date_settings;
DROP TABLE archiving_utilisation_settings;
RENAME TABLE document_archiving TO document_archiving_link;
ALTER TABLE document_archiving_link DROP COLUMN archiving_type_id;
ALTER TABLE document_text ADD INDEX document_text_document_id_indx (document_id);
ALTER TABLE help MODIFY id INTEGER NOT NULL UNIQUE AUTO_INCREMENT;
ALTER TABLE search_document_user_link ADD INDEX search_document_user_link_user_id_indx (user_id);
ALTER TABLE search_document_user_link ADD INDEX search_document_user_link_document_id_indx (document_id);
DROP TABLE site_sections_lookup;
DROP TABLE site_access_lookup;
DROP TABLE sitemap;
DROP TABLE sys_deleted;
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sxw', 'application/vnd.sun.xml.writer', 'icons/oowriter.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('stw','application/vnd.sun.xml.writer.template', 'icons/oowriter.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sxc','application/vnd.sun.xml.calc', 'icons/oocalc.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('stc','application/vnd.sun.xml.calc.template', 'icons/oocalc.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sxd','application/vnd.sun.xml.draw', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('std','application/vnd.sun.xml.draw.template', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sxi','application/vnd.sun.xml.impress', 'icons/ooimpress.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sti','application/vnd.sun.xml.impress.template', 'icons/ooimpress.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sxg','application/vnd.sun.xml.writer.global', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sxm','application/vnd.sun.xml.math', NULL);
INSERT INTO system_settings (name, value) values ("knowledgeTreeVersion", "1.2.0");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Expunge");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Force CheckIn");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Email Link");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Collaboration Step Approve");
INSERT INTO help VALUES (1,'browse','dochelp.html');
INSERT INTO help VALUES (2,'dashboard','dashboardHelp.html');
INSERT INTO help VALUES (3,'addFolder','addFolderHelp.html');
INSERT INTO help VALUES (4,'editFolder','editFolderHelp.html');
INSERT INTO help VALUES (5,'addFolderCollaboration','addFolderCollaborationHelp.html');
INSERT INTO help VALUES (6,'modifyFolderCollaboration','addFolderCollaborationHelp.html');
INSERT INTO help VALUES (7,'addDocument','addDocumentHelp.html');
INSERT INTO help VALUES (8,'viewDocument','viewDocumentHelp.html');
INSERT INTO help VALUES (9,'modifyDocument','modifyDocumentHelp.html');
INSERT INTO help VALUES (10,'modifyDocumentRouting','modifyDocumentRoutingHelp.html');
INSERT INTO help VALUES (11,'emailDocument','emailDocumentHelp.html');
INSERT INTO help VALUES (12,'deleteDocument','deleteDocumentHelp.html');
INSERT INTO help VALUES (13,'administration','administrationHelp.html');
INSERT INTO help VALUES (14,'addGroup','addGroupHelp.html');
INSERT INTO help VALUES (15,'editGroup','editGroupHelp.html');
INSERT INTO help VALUES (16,'removeGroup','removeGroupHelp.html');
INSERT INTO help VALUES (17,'assignGroupToUnit','assignGroupToUnitHelp.html');
INSERT INTO help VALUES (18,'removeGroupFromUnit','removeGroupFromUnitHelp.html');
INSERT INTO help VALUES (19,'addUnit','addUnitHelp.html');
INSERT INTO help VALUES (20,'editUnit','editUnitHelp.html');
INSERT INTO help VALUES (21,'removeUnit','removeUnitHelp.html');
INSERT INTO help VALUES (22,'addOrg','addOrgHelp.html');
INSERT INTO help VALUES (23,'editOrg','editOrgHelp.html');
INSERT INTO help VALUES (24,'removeOrg','removeOrgHelp.html');
INSERT INTO help VALUES (25,'addRole','addRoleHelp.html');
INSERT INTO help VALUES (26,'editRole','editRoleHelp.html');
INSERT INTO help VALUES (27,'removeRole','removeRoleHelp.html');
INSERT INTO help VALUES (28,'addLink','addLinkHelp.html');
INSERT INTO help VALUES (29,'addLinkSuccess','addLinkHelp.html');
INSERT INTO help VALUES (30,'editLink','editLinkHelp.html');
INSERT INTO help VALUES (31,'removeLink','removeLinkHelp.html');
INSERT INTO help VALUES (32,'systemAdministration','systemAdministrationHelp.html');
INSERT INTO help VALUES (33,'deleteFolder','deleteFolderHelp.html');
INSERT INTO help VALUES (34,'editDocType','editDocTypeHelp.html');
INSERT INTO help VALUES (35,'removeDocType','removeDocTypeHelp.html');
INSERT INTO help VALUES (36,'addDocType','addDocTypeHelp.html');
INSERT INTO help VALUES (37,'addDocTypeSuccess','addDocTypeHelp.html');
INSERT INTO help VALUES (38,'manageSubscriptions','manageSubscriptionsHelp.html');
INSERT INTO help VALUES (39,'addSubscription','addSubscriptionHelp.html');
INSERT INTO help VALUES (40,'removeSubscription','removeSubscriptionHelp.html');
INSERT INTO help VALUES (41,'preferences','preferencesHelp.html');
INSERT INTO help VALUES (42,'editPrefsSuccess','preferencesHelp.html');
INSERT INTO help VALUES (43,'modifyDocumentGenericMetaData','modifyDocumentGenericMetaDataHelp.html');
INSERT INTO help VALUES (44,'viewHistory','viewHistoryHelp.html');
INSERT INTO help VALUES (45,'checkInDocument','checkInDocumentHelp.html');
INSERT INTO help VALUES (46,'checkOutDocument','checkOutDocumentHelp.html');
INSERT INTO help VALUES (47,'advancedSearch','advancedSearchHelp.html');
INSERT INTO help VALUES (48,'deleteFolderCollaboration','deleteFolderCollaborationHelp.html');
INSERT INTO help VALUES (49,'addFolderDocType','addFolderDocTypeHelp.html');
INSERT INTO help VALUES (50,'deleteFolderDocType','deleteFolderDocTypeHelp.html');
INSERT INTO help VALUES (51,'addGroupFolderLink','addGroupFolderLinkHelp.html');
INSERT INTO help VALUES (52,'deleteGroupFolderLink','deleteGroupFolderLinkHelp.html');
INSERT INTO help VALUES (53,'addWebsite','addWebsiteHelp.html');
INSERT INTO help VALUES (54,'addWebsiteSuccess','addWebsiteHelp.html');
INSERT INTO help VALUES (55,'editWebsite','editWebsiteHelp.html');
INSERT INTO help VALUES (56,'removeWebSite','removeWebSiteHelp.html');
INSERT INTO help VALUES (57,'standardSearch','standardSearchHelp.html');
INSERT INTO help VALUES (58,'modifyDocumentTypeMetaData','modifyDocumentTypeMetaDataHelp.html');
INSERT INTO help VALUES (59,'addDocField','addDocFieldHelp.html');
INSERT INTO help VALUES (60,'editDocField','editDocFieldHelp.html');
INSERT INTO help VALUES (61,'removeDocField','removeDocFieldHelp.html');
INSERT INTO help VALUES (62,'addMetaData','addMetaDataHelp.html');
INSERT INTO help VALUES (63,'editMetaData','editMetaDataHelp.html');
INSERT INTO help VALUES (64,'removeMetaData','removeMetaDataHelp.html');
INSERT INTO help VALUES (65,'addUser','addUserHelp.html');
INSERT INTO help VALUES (66,'editUser','editUserHelp.html');
INSERT INTO help VALUES (67,'removeUser','removeUserHelp.html');
INSERT INTO help VALUES (68,'addUserToGroup','addUserToGroupHelp.html');
INSERT INTO help VALUES (69,'removeUserFromGroup','removeUserFromGroupHelp.html');
INSERT INTO help VALUES (70,'viewDiscussion','viewDiscussionThread.html');
INSERT INTO help VALUES (71,'addComment','addDiscussionComment.html');
INSERT INTO help VALUES (72,'listNews','listDashboardNewsHelp.html');
INSERT INTO help VALUES (73,'editNews','editDashboardNewsHelp.html');
INSERT INTO help VALUES (74,'previewNews','previewDashboardNewsHelp.html');
INSERT INTO help VALUES (75,'addNews','addDashboardNewsHelp.html');
INSERT INTO help VALUES (76,'modifyDocumentArchiveSettings','modifyDocumentArchiveSettingsHelp.html');
INSERT INTO help VALUES (77,'addDocumentArchiveSettings','addDocumentArchiveSettingsHelp.html');
INSERT INTO help VALUES (78,'listDocFields','listDocumentFieldsAdmin.html');
INSERT INTO help VALUES (79,'editDocFieldLookups','editDocFieldLookups.html');
INSERT INTO help VALUES (80,'addMetaDataForField','addMetaDataForField.html'); 
INSERT INTO help VALUES (81,'editMetaDataForField','editMetaDataForField.html'); 
INSERT INTO help VALUES (82,'removeMetaDataFromField','removeMetaDataFromField.html'); 
INSERT INTO help VALUES (83,'listDocs','listDocumentsCheckoutHelp.html'); 
INSERT INTO help VALUES (84,'editDocCheckout','editDocCheckoutHelp.html'); 
INSERT INTO help VALUES (85,'listDocTypes','listDocTypesHelp.html'); 
INSERT INTO help VALUES (86,'editDocTypeFields','editDocFieldHelp.html'); 
INSERT INTO help VALUES (87,'addDocTypeFieldsLink','addDocTypeFieldHelp.html'); 
INSERT INTO help VALUES (88,'listGroups','listGroupsHelp.html'); 
INSERT INTO help VALUES (89,'editGroupUnit','editGroupUnitHelp.html'); 
INSERT INTO help VALUES (90,'listOrg','listOrgHelp.html'); 
INSERT INTO help VALUES (91,'listRole','listRolesHelp.html'); 
INSERT INTO help VALUES (92,'listUnits','listUnitHelp.html'); 
INSERT INTO help VALUES (93,'editUnitOrg','editUnitOrgHelp.html'); 
INSERT INTO help VALUES (94,'removeUnitFromOrg','removeUnitFromOrgHelp.html'); 
INSERT INTO help VALUES (95,'addUnitToOrg','addUnitToOrgHelp.html'); 
INSERT INTO help VALUES (96,'listUsers','listUsersHelp.html'); 
INSERT INTO help VALUES (97,'editUserGroups','editUserGroupsHelp.html'); 
INSERT INTO help VALUES (98,'listWebsites','listWebsitesHelp.html');

ALTER TABLE folders ADD COLUMN inherit_parent_folder_permission BIT;
UPDATE folders SET inherit_parent_folder_permission = 1;

DELETE FROM organisations_lookup;
DELETE FROM units_lookup;
DELETE FROM units_organisations_link;
DELETE FROM groups_lookup;
DELETE FROM groups_units_link;
DELETE FROM users;
DELETE FROM users_groups_link;
DELETE FROM folders;
DELETE FROM document_types_lookup;

-- organisation
INSERT INTO organisations_lookup (name) VALUES ("Default Organisation");
-- units
INSERT INTO units_lookup (name) VALUES ("Default Unit");
INSERT INTO units_organisations_link (unit_id, organisation_id) VALUES (1, 1);
-- setup groups
INSERT INTO groups_lookup (id, name, is_sys_admin, is_unit_admin) VALUES (1,"System Administrators", 1, 0); -- id=1
INSERT INTO groups_lookup (id, name, is_sys_admin, is_unit_admin) VALUES (2,"Unit Administrators", 0, 1); -- id=2
INSERT INTO groups_lookup (id, name, is_sys_admin, is_unit_admin) VALUES (3,"Anonymous", 0, 0); -- id=3
-- unit administrators
INSERT INTO groups_units_link (group_id, unit_id) VALUES (2, 1);
-- system administrator
-- passwords are md5'ed
INSERT INTO users (id, username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES (1,"admin", "Administrator", "21232f297a57a5a743894a0e4a801fc3", "0", "0", "", "", 1, 1, "", 1, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (1, 1);
-- unit administrator
INSERT INTO users (id, username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES (2,"unitAdmin", "Unit Administrator", "21232f297a57a5a743894a0e4a801fc3", "0", "0", "", "", 1, 1, "", 1, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (2, 2);
-- guest user
INSERT INTO users (id, username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES (3,"guest", "Anonymous", "084e0343a0486ff05530df6c705c8bb4", "0", "0", "", "", 0, 0, "", 19, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (3, 3);
-- define folder structure
INSERT INTO folders (id,name, description, parent_id, creator_id, unit_id, is_public)
             VALUES (1,"Root Folder", "Root Document Folder", 0, 1, 0, 0);
INSERT INTO folders (name, description, parent_id, creator_id, unit_id, is_public, parent_folder_ids, full_path)
             VALUES ("Default Unit", "Default Unit Root Folder", 1, 1, 1, 0, "1", "Root Folder");