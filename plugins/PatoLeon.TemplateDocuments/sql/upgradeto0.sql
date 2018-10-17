INSERT INTO config_settings (group_name, display_name, description, item, value, default_value, type, options, can_edit)
VALUES ('KnowledgeTree', 'Zoho API KEY', 'Zoho API Key used to connect to Zoho services', 'zoho_api_key', '', '', 'string', '', 0);

INSERT INTO config_settings (group_name, display_name, description, item, value, default_value, type, options, can_edit)
VALUES ('KnowledgeTree', 'Template documents for Zoho', 'Document Type used for Zoho templates', 'zoho_template_doc_type', '', '', 'numeric_string', '', 0);

INSERT INTO config_settings (group_name, display_name, description, item, value, default_value, type, options, can_edit)
VALUES ('KnowledgeTree', 'Template documents for document generation', 'Document Type used for generated documents', 'templates_doc_type', '', '', 'numeric_string', '', 0);


CREATE TABLE `template_documents_merge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `name` char(100) NOT NULL,
  `description` text NOT NULL,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_documents_forms_un` (`document_id`),
  CONSTRAINT `template_documents_forms_documents_fk` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;