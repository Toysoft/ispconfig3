ALTER TABLE  `client_template` ADD  `limit_openvz_vm` int(11) NOT NULL DEFAULT '0' AFTER  `limit_maildomain` ,
ADD  `limit_openvz_vm_template_id` int(11) NOT NULL DEFAULT '0' AFTER  `limit_openvz_vm`;