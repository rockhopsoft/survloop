
ALTER TABLE SL_AddyGeo RENAME TO sl_addy_geo;
ALTER TABLE SL_BusRules RENAME TO sl_bus_rules;
ALTER TABLE SL_Caches RENAME TO sl_caches;
ALTER TABLE SL_CampaignClicks RENAME TO sl_campaign_clicks;
ALTER TABLE SL_Campaigns RENAME TO sl_campaigns;
ALTER TABLE SL_Conditions RENAME TO sl_conditions;
ALTER TABLE SL_ConditionsArticles RENAME TO sl_conditions_articles;
ALTER TABLE SL_ConditionsNodes RENAME TO sl_conditions_nodes;
ALTER TABLE SL_ConditionsVals RENAME TO sl_conditions_vals;
ALTER TABLE SL_Contact RENAME TO sl_contact;
ALTER TABLE SL_Databases RENAME TO sl_databases;
ALTER TABLE SL_DataHelpers RENAME TO sl_data_helpers;
ALTER TABLE SL_DataLinks RENAME TO sl_data_links;
ALTER TABLE SL_DataLoop RENAME TO sl_data_loop;
ALTER TABLE SL_DataSubsets RENAME TO sl_data_subsets;
ALTER TABLE SL_Definitions RENAME TO sl_definitions;
ALTER TABLE SL_DesignTweaks RENAME TO sl_design_tweaks;
ALTER TABLE SL_Emailed RENAME TO sl_emailed;
ALTER TABLE SL_Emails RENAME TO sl_emails;
ALTER TABLE SL_Fields RENAME TO sl_fields;
ALTER TABLE SL_Images RENAME TO sl_images;
ALTER TABLE SL_LogActions RENAME TO sl_log_actions;
ALTER TABLE SL_Node RENAME TO sl_node;
ALTER TABLE SL_NodeResponses RENAME TO sl_node_responses;
ALTER TABLE SL_NodeSaves RENAME TO sl_node_saves;
ALTER TABLE SL_NodeSavesPage RENAME TO sl_node_saves_page;
ALTER TABLE SL_SearchRecDump RENAME TO sl_search_rec_dump;
ALTER TABLE SL_Sess RENAME TO sl_sess;
ALTER TABLE SL_SessEmojis RENAME TO sl_sess_emojis;
ALTER TABLE SL_SessLoops RENAME TO sl_sess_loops;
ALTER TABLE SL_SessPage RENAME TO sl_sess_page;
ALTER TABLE SL_SessSite RENAME TO sl_sess_site;
ALTER TABLE SL_Tables RENAME TO sl_tables;
ALTER TABLE SL_ThreadComments RENAME TO sl_thread_comments;
ALTER TABLE SL_ThreadFollows RENAME TO sl_thread_follows;
ALTER TABLE SL_ThreadLikes RENAME TO sl_thread_likes;
ALTER TABLE SL_Threads RENAME TO sl_threads;
ALTER TABLE SL_Tokens RENAME TO sl_tokens;
ALTER TABLE SL_Tree RENAME TO sl_tree;
ALTER TABLE SL_Uploads RENAME TO sl_uploads;
ALTER TABLE SL_UploadsTime RENAME TO sl_uploads_time;
ALTER TABLE SL_UsersActivity RENAME TO sl_users_activity;
ALTER TABLE SL_UsersRoles RENAME TO sl_users_roles;
ALTER TABLE SL_ZipAshrae RENAME TO sl_zip_ashrae;
ALTER TABLE SL_Zips RENAME TO sl_zips;


ALTER TABLE sl_addy_geo CHANGE `AdyGeoID` `ady_geo_id` int(11);
ALTER TABLE sl_addy_geo CHANGE `AdyGeoAddress` `ady_geo_address` varchar(255);
ALTER TABLE sl_addy_geo CHANGE `AdyGeoLat` `ady_geo_lat` varchar(255);
ALTER TABLE sl_addy_geo CHANGE `AdyGeoLong` `ady_geo_long` varchar(255);

ALTER TABLE sl_bus_rules CHANGE `RuleID` `rule_id` int(11);
ALTER TABLE sl_bus_rules CHANGE `RuleDatabase` `rule_database` int(11);
ALTER TABLE sl_bus_rules CHANGE `RuleStatement` `rule_statement` longtext;
ALTER TABLE sl_bus_rules CHANGE `RuleConstraint` `rule_constraint` longtext;
ALTER TABLE sl_bus_rules CHANGE `RuleTables` `rule_tables` varchar(255);
ALTER TABLE sl_bus_rules CHANGE `RuleFields` `rule_fields` varchar(255);
ALTER TABLE sl_bus_rules CHANGE `RuleIsAppOrient` `rule_is_app_orient` tinyint(1);
ALTER TABLE sl_bus_rules CHANGE `RuleIsRelation` `rule_is_relation` tinyint(1);
ALTER TABLE sl_bus_rules CHANGE `RuleTestOn` `rule_test_on` varchar(10);
ALTER TABLE sl_bus_rules CHANGE `RulePhys` `rule_phys` int(11);
ALTER TABLE sl_bus_rules CHANGE `RuleLogic` `rule_logic` int(11);
ALTER TABLE sl_bus_rules CHANGE `RuleRel` `rule_rel` int(11);
ALTER TABLE sl_bus_rules CHANGE `RuleAction` `rule_action` longtext;


ALTER TABLE sl_caches CHANGE `CachID` `cach_id` int(11);
ALTER TABLE sl_caches CHANGE `CachType` `cach_type` varchar(12);
ALTER TABLE sl_caches CHANGE `CachTreeID` `cach_tree_id` int(11);
ALTER TABLE sl_caches CHANGE `CachRecID` `cach_rec_id` int(11);
ALTER TABLE sl_caches CHANGE `CachKey` `cach_key` text;
ALTER TABLE sl_caches CHANGE `CachValue` `cach_value` longtext;
ALTER TABLE sl_caches CHANGE `CachCss` `cach_css` longtext;
ALTER TABLE sl_caches CHANGE `CachJs` `cach_js` longtext;

ALTER TABLE sl_campaign_clicks CHANGE `CmpClkID` `cmp_clk_id` int(11);
ALTER TABLE sl_campaign_clicks CHANGE `CmpClkCampaignID` `cmp_clk_campaign_id` int(11);
ALTER TABLE sl_campaign_clicks CHANGE `CmpClkFromUrl` `cmp_clk_from_url` text;
ALTER TABLE sl_campaign_clicks CHANGE `CmpClkToUrl` `cmp_clk_to_url` text;

ALTER TABLE sl_campaigns CHANGE `CampID` `camp_id` int(11);
ALTER TABLE sl_campaigns CHANGE `CampName` `camp_name` varchar(255);

ALTER TABLE sl_conditions CHANGE `CondID` `cond_id` int(11);
ALTER TABLE sl_conditions CHANGE `CondDatabase` `cond_database` int(11);
ALTER TABLE sl_conditions CHANGE `CondTag` `cond_tag` varchar(100);
ALTER TABLE sl_conditions CHANGE `CondDesc` `cond_desc` longtext;
ALTER TABLE sl_conditions CHANGE `CondOperator` `cond_operator` varchar(50);
ALTER TABLE sl_conditions CHANGE `CondOperDeet` `cond_oper_deet` varchar(100);
ALTER TABLE sl_conditions CHANGE `CondField` `cond_field` int(11);
ALTER TABLE sl_conditions CHANGE `CondTable` `cond_table` int(11);
ALTER TABLE sl_conditions CHANGE `CondLoop` `cond_loop` int(11);
ALTER TABLE sl_conditions CHANGE `CondOpts` `cond_opts` int(11);

ALTER TABLE sl_conditions_articles CHANGE `ArticleID` `article_id` int(11);
ALTER TABLE sl_conditions_articles CHANGE `ArticleCondID` `article_cond_id` int(11);
ALTER TABLE sl_conditions_articles CHANGE `ArticleURL` `article_url` varchar(255);
ALTER TABLE sl_conditions_articles CHANGE `ArticleTitle` `article_title` varchar(255);

ALTER TABLE sl_conditions_nodes CHANGE `CondNodeID` `cond_node_id` int(11);
ALTER TABLE sl_conditions_nodes CHANGE `CondNodeCondID` `cond_node_cond_id` int(11);
ALTER TABLE sl_conditions_nodes CHANGE `CondNodeNodeID` `cond_node_node_id` int(11);
ALTER TABLE sl_conditions_nodes CHANGE `CondNodeLoopID` `cond_node_loop_id` int(11);

ALTER TABLE sl_conditions_vals CHANGE `CondValID` `cond_val_id` int(11);
ALTER TABLE sl_conditions_vals CHANGE `CondValCondID` `cond_val_cond_id` int(11);
ALTER TABLE sl_conditions_vals CHANGE `CondValValue` `cond_val_value` varchar(255);

ALTER TABLE sl_contact CHANGE `ContID` `cont_id` int(11);
ALTER TABLE sl_contact CHANGE `ContType` `cont_type` varchar(255);
ALTER TABLE sl_contact CHANGE `ContFlag` `cont_flag` varchar(255);
ALTER TABLE sl_contact CHANGE `ContEmail` `cont_email` varchar(255);
ALTER TABLE sl_contact CHANGE `ContSubject` `cont_subject` varchar(255);
ALTER TABLE sl_contact CHANGE `ContBody` `cont_body` text;

ALTER TABLE sl_databases CHANGE `DbID` `db_id` int(11);
ALTER TABLE sl_databases CHANGE `DbUser` `db_user` int(11);
ALTER TABLE sl_databases CHANGE `DbPrefix` `db_prefix` varchar(25);
ALTER TABLE sl_databases CHANGE `DbName` `db_name` varchar(255);
ALTER TABLE sl_databases CHANGE `DbDesc` `db_desc` longtext;
ALTER TABLE sl_databases CHANGE `DbMission` `db_mission` longtext;
ALTER TABLE sl_databases CHANGE `DbOpts` `db_opts` int(11);
ALTER TABLE sl_databases CHANGE `DbTables` `db_tables` int(11);
ALTER TABLE sl_databases CHANGE `DbFields` `db_fields` int(11);

ALTER TABLE sl_data_helpers CHANGE `DataHelpID` `data_help_id` int(11);
ALTER TABLE sl_data_helpers CHANGE `DataHelpTree` `data_help_tree` int(11);
ALTER TABLE sl_data_helpers CHANGE `DataHelpParentTable` `data_help_parent_table` varchar(50);
ALTER TABLE sl_data_helpers CHANGE `DataHelpTable` `data_help_table` varchar(50);
ALTER TABLE sl_data_helpers CHANGE `DataHelpKeyField` `data_help_key_field` varchar(50);
ALTER TABLE sl_data_helpers CHANGE `DataHelpValueField` `data_help_value_field` varchar(50);

ALTER TABLE sl_data_links CHANGE `DataLinkID` `data_link_id` int(11);
ALTER TABLE sl_data_links CHANGE `DataLinkTree` `data_link_tree` int(11);
ALTER TABLE sl_data_links CHANGE `DataLinkTable` `data_link_table` int(11);

ALTER TABLE sl_data_loop CHANGE `DataLoopID` `data_loop_id` int(11);
ALTER TABLE sl_data_loop CHANGE `DataLoopTree` `data_loop_tree` int(11);
ALTER TABLE sl_data_loop CHANGE `DataLoopRoot` `data_loop_root` int(11);
ALTER TABLE sl_data_loop CHANGE `DataLoopPlural` `data_loop_plural` varchar(50);
ALTER TABLE sl_data_loop CHANGE `DataLoopSingular` `data_loop_singular` varchar(50);
ALTER TABLE sl_data_loop CHANGE `DataLoopTable` `data_loop_table` varchar(100);
ALTER TABLE sl_data_loop CHANGE `DataLoopSortFld` `data_loop_sort_fld` varchar(100);
ALTER TABLE sl_data_loop CHANGE `DataLoopDoneFld` `data_loop_done_fld` varchar(100);
ALTER TABLE sl_data_loop CHANGE `DataLoopMaxLimit` `data_loop_max_limit` int(11);
ALTER TABLE sl_data_loop CHANGE `DataLoopWarnLimit` `data_loop_warn_limit` int(11);
ALTER TABLE sl_data_loop CHANGE `DataLoopMinLimit` `data_loop_min_limit` int(11);
ALTER TABLE sl_data_loop CHANGE `DataLoopIsStep` `data_loop_is_step` tinyint(1);
ALTER TABLE sl_data_loop CHANGE `DataLoopAutoGen` `data_loop_auto_gen` tinyint(1);

ALTER TABLE sl_data_subsets CHANGE `DataSubID` `data_sub_id` int(11);
ALTER TABLE sl_data_subsets CHANGE `DataSubTree` `data_sub_tree` int(11);
ALTER TABLE sl_data_subsets CHANGE `DataSubTbl` `data_sub_tbl` varchar(100);
ALTER TABLE sl_data_subsets CHANGE `DataSubTblLnk` `data_sub_tbl_lnk` varchar(100);
ALTER TABLE sl_data_subsets CHANGE `DataSubSubTbl` `data_sub_sub_tbl` varchar(50);
ALTER TABLE sl_data_subsets CHANGE `DataSubSubLnk` `data_sub_sub_lnk` varchar(100);
ALTER TABLE sl_data_subsets CHANGE `DataSubAutoGen` `data_sub_auto_gen` tinyint(1);

ALTER TABLE sl_definitions CHANGE `DefID` `def_id` int(11);
ALTER TABLE sl_definitions CHANGE `DefDatabase` `def_database` int(11);
ALTER TABLE sl_definitions CHANGE `DefSet` `def_set` varchar(25);
ALTER TABLE sl_definitions CHANGE `DefSubset` `def_subset` varchar(50);
ALTER TABLE sl_definitions CHANGE `DefOrder` `def_order` int(11);
ALTER TABLE sl_definitions CHANGE `DefIsActive` `def_is_active` tinyint(1);
ALTER TABLE sl_definitions CHANGE `DefValue` `def_value` varchar(255);
ALTER TABLE sl_definitions CHANGE `DefDescription` `def_description` longtext;

ALTER TABLE sl_design_tweaks CHANGE `TweakID` `twk_id` int(11);
ALTER TABLE sl_design_tweaks CHANGE `TweakVersionAB` `twk_version_ab` varchar(255);
ALTER TABLE sl_design_tweaks CHANGE `TweakSubmissionProgress` `twk_submission_progress` int(11);
ALTER TABLE sl_design_tweaks CHANGE `TweakUserID` `twk_user_id` bigint(20);
ALTER TABLE sl_design_tweaks CHANGE `TweakUniqueStr` `twk_unique_str` varchar(20);
ALTER TABLE sl_design_tweaks CHANGE `TweakIPaddy` `twk_ip_addy` varchar(255);
ALTER TABLE sl_design_tweaks CHANGE `TweakIsMobile` `twk_is_mobile` tinyint(1);

ALTER TABLE sl_emailed CHANGE `EmailedID` `emailed_id` int(11);
ALTER TABLE sl_emailed CHANGE `EmailedTree` `emailed_tree` int(11);
ALTER TABLE sl_emailed CHANGE `EmailedRecID` `emailed_rec_id` int(11);
ALTER TABLE sl_emailed CHANGE `EmailedEmailID` `emailed_email_id` int(11);
ALTER TABLE sl_emailed CHANGE `EmailedTo` `emailed_to` varchar(255);
ALTER TABLE sl_emailed CHANGE `EmailedToUser` `emailed_to_user` int(11);
ALTER TABLE sl_emailed CHANGE `EmailedFromUser` `emailed_from_user` int(11);
ALTER TABLE sl_emailed CHANGE `EmailedSubject` `emailed_subject` varchar(255);
ALTER TABLE sl_emailed CHANGE `EmailedBody` `emailed_body` longtext;
ALTER TABLE sl_emailed CHANGE `EmailedOpts` `emailed_opts` int(11);

ALTER TABLE sl_emails CHANGE `EmailID` `email_id` int(11);
ALTER TABLE sl_emails CHANGE `EmailTree` `email_tree` int(11);
ALTER TABLE sl_emails CHANGE `EmailType` `email_type` varchar(255);
ALTER TABLE sl_emails CHANGE `EmailName` `email_name` varchar(255);
ALTER TABLE sl_emails CHANGE `EmailSubject` `email_subject` longtext;
ALTER TABLE sl_emails CHANGE `EmailBody` `email_body` longtext;
ALTER TABLE sl_emails CHANGE `EmailOpts` `email_opts` int(11);
ALTER TABLE sl_emails CHANGE `EmailTotSent` `email_tot_sent` int(11);

ALTER TABLE sl_fields CHANGE `FldID` `fld_id` int(11);
ALTER TABLE sl_fields CHANGE `FldDatabase` `fld_database` int(11);
ALTER TABLE sl_fields CHANGE `FldTable` `fld_table` int(11);
ALTER TABLE sl_fields CHANGE `FldOrd` `fld_ord` int(11);
ALTER TABLE sl_fields CHANGE `FldSpecType` `fld_spec_type` varchar(10);
ALTER TABLE sl_fields CHANGE `FldSpecSource` `fld_spec_source` int(11);
ALTER TABLE sl_fields CHANGE `FldName` `fld_name` varchar(255);
ALTER TABLE sl_fields CHANGE `FldEng` `fld_eng` varchar(255);
ALTER TABLE sl_fields CHANGE `FldAlias` `fld_alias` varchar(255);
ALTER TABLE sl_fields CHANGE `FldDesc` `fld_desc` longtext;
ALTER TABLE sl_fields CHANGE `FldNotes` `fld_notes` longtext;
ALTER TABLE sl_fields CHANGE `FldForeignTable` `fld_foreign_table` int(11);
ALTER TABLE sl_fields CHANGE `FldForeignMin` `fld_foreign_min` varchar(255);
ALTER TABLE sl_fields CHANGE `FldForeignMax` `fld_foreign_max` varchar(255);
ALTER TABLE sl_fields CHANGE `FldForeign2Min` `fld_foreign2_min` varchar(255);
ALTER TABLE sl_fields CHANGE `FldForeign2Max` `fld_foreign2_max` varchar(255);
ALTER TABLE sl_fields CHANGE `FldValues` `fld_values` longtext;
ALTER TABLE sl_fields CHANGE `FldDefault` `fld_default` varchar(255);
ALTER TABLE sl_fields CHANGE `FldIsIndex` `fld_is_index` tinyint(1);
ALTER TABLE sl_fields CHANGE `FldType` `fld_type` varchar(25);
ALTER TABLE sl_fields CHANGE `FldDataType` `fld_data_type` varchar(25);
ALTER TABLE sl_fields CHANGE `FldDataLength` `fld_data_length` int(11);
ALTER TABLE sl_fields CHANGE `FldDataDecimals` `fld_data_decimals` int(11);
ALTER TABLE sl_fields CHANGE `FldCharSupport` `fld_char_support` varchar(255);
ALTER TABLE sl_fields CHANGE `FldInputMask` `fld_input_mask` varchar(255);
ALTER TABLE sl_fields CHANGE `FldDisplayFormat` `fld_display_format` varchar(255);
ALTER TABLE sl_fields CHANGE `FldKeyType` `fld_key_type` varchar(255);
ALTER TABLE sl_fields CHANGE `FldKeyStruct` `fld_key_struct` varchar(10);
ALTER TABLE sl_fields CHANGE `FldEditRule` `fld_edit_rule` varchar(10);
ALTER TABLE sl_fields CHANGE `FldUnique` `fld_unique` tinyint(1);
ALTER TABLE sl_fields CHANGE `FldNullSupport` `fld_null_support` tinyint(1);
ALTER TABLE sl_fields CHANGE `FldValuesEnteredBy` `fld_values_entered_by` varchar(10);
ALTER TABLE sl_fields CHANGE `FldRequired` `fld_required` tinyint(1);
ALTER TABLE sl_fields CHANGE `FldCompareSame` `fld_compare_same` int(11);
ALTER TABLE sl_fields CHANGE `FldCompareOther` `fld_compare_other` int(11);
ALTER TABLE sl_fields CHANGE `FldCompareValue` `fld_compare_value` int(11);
ALTER TABLE sl_fields CHANGE `FldOperateSame` `fld_operate_same` int(11);
ALTER TABLE sl_fields CHANGE `FldOperateOther` `fld_operate_other` int(11);
ALTER TABLE sl_fields CHANGE `FldOperateValue` `fld_operate_value` int(11);
ALTER TABLE sl_fields CHANGE `FldOpts` `fld_opts` int(11);

ALTER TABLE sl_images CHANGE `ImgID` `img_id` int(11);
ALTER TABLE sl_images CHANGE `ImgDatabaseID` `img_database_id` int(11);
ALTER TABLE sl_images CHANGE `ImgUserID` `img_user_id` bigint(20);
ALTER TABLE sl_images CHANGE `ImgFileOrig` `img_file_orig` varchar(255);
ALTER TABLE sl_images CHANGE `ImgFileLoc` `img_file_loc` varchar(255);
ALTER TABLE sl_images CHANGE `ImgFullFilename` `img_full_filename` varchar(255);
ALTER TABLE sl_images CHANGE `ImgTitle` `img_title` varchar(255);
ALTER TABLE sl_images CHANGE `ImgCredit` `img_credit` varchar(255);
ALTER TABLE sl_images CHANGE `ImgCreditUrl` `img_credit_url` varchar(255);
ALTER TABLE sl_images CHANGE `ImgNodeID` `img_node_id` varchar(255);
ALTER TABLE sl_images CHANGE `ImgType` `img_type` varchar(10);
ALTER TABLE sl_images CHANGE `ImgFileSize` `img_file_size` int(11);
ALTER TABLE sl_images CHANGE `ImgWidth` `img_width` int(11);
ALTER TABLE sl_images CHANGE `ImgHeight` `img_height` int(11);

ALTER TABLE sl_log_actions CHANGE `LogID` `log_id` int(11);
ALTER TABLE sl_log_actions CHANGE `LogUser` `log_user` int(11);
ALTER TABLE sl_log_actions CHANGE `LogDatabase` `log_database` int(11);
ALTER TABLE sl_log_actions CHANGE `LogTable` `log_table` int(11);
ALTER TABLE sl_log_actions CHANGE `LogField` `log_field` int(11);
ALTER TABLE sl_log_actions CHANGE `LogAction` `log_action` varchar(25);
ALTER TABLE sl_log_actions CHANGE `LogOldName` `log_old_name` varchar(255);
ALTER TABLE sl_log_actions CHANGE `LogNewName` `log_new_name` varchar(255);

ALTER TABLE sl_node CHANGE `NodeID` `node_id` int(11);
ALTER TABLE sl_node CHANGE `NodeTree` `node_tree` int(11);
ALTER TABLE sl_node CHANGE `NodeParentID` `node_parent_id` int(11);
ALTER TABLE sl_node CHANGE `NodeParentOrder` `node_parent_order` int(11);
ALTER TABLE sl_node CHANGE `NodeType` `node_type` varchar(25);
ALTER TABLE sl_node CHANGE `NodePromptText` `node_prompt_text` longtext;
ALTER TABLE sl_node CHANGE `NodePromptNotes` `node_prompt_notes` longtext;
ALTER TABLE sl_node CHANGE `NodePromptAfter` `node_prompt_after` longtext;
ALTER TABLE sl_node CHANGE `NodeInternalNotes` `node_internal_notes` longtext;
ALTER TABLE sl_node CHANGE `NodeResponseSet` `node_response_set` varchar(50);
ALTER TABLE sl_node CHANGE `NodeDefault` `node_default` varchar(255);
ALTER TABLE sl_node CHANGE `NodeDataBranch` `node_data_branch` varchar(100);
ALTER TABLE sl_node CHANGE `NodeDataStore` `node_data_store` varchar(100);
ALTER TABLE sl_node CHANGE `NodeTextSuggest` `node_text_suggest` varchar(100);
ALTER TABLE sl_node CHANGE `NodeCharLimit` `node_char_limit` int(11);
ALTER TABLE sl_node CHANGE `NodeLikes` `node_likes` int(11);
ALTER TABLE sl_node CHANGE `NodeDislikes` `node_dislikes` int(11);
ALTER TABLE sl_node CHANGE `NodeOpts` `node_opts` int(11);


ALTER TABLE sl_node_responses CHANGE `NodeResID` `node_res_id` int(11);
ALTER TABLE sl_node_responses CHANGE `NodeResNode` `node_res_node` int(11);
ALTER TABLE sl_node_responses CHANGE `NodeResOrd` `node_res_ord` int(11);
ALTER TABLE sl_node_responses CHANGE `NodeResEng` `node_res_eng` text;
ALTER TABLE sl_node_responses CHANGE `NodeResValue` `node_res_value` varchar(255);
ALTER TABLE sl_node_responses CHANGE `NodeResShowKids` `node_res_show_kids` int(11);
ALTER TABLE sl_node_responses CHANGE `NodeResMutEx` `node_res_mut_ex` tinyint(1);

ALTER TABLE sl_node_saves CHANGE `NodeSaveID` `node_save_id` int(11);
ALTER TABLE sl_node_saves CHANGE `NodeSaveSession` `node_save_session` int(11);
ALTER TABLE sl_node_saves CHANGE `NodeSaveLoopItemID` `node_save_loop_item_id` int(11);
ALTER TABLE sl_node_saves CHANGE `NodeSaveNode` `node_save_node` int(11);
ALTER TABLE sl_node_saves CHANGE `NodeSaveVersionAB` `node_save_version_ab` varchar(255);
ALTER TABLE sl_node_saves CHANGE `NodeSaveTblFld` `node_save_tbl_fld` varchar(100);
ALTER TABLE sl_node_saves CHANGE `NodeSaveNewVal` `node_save_new_val` longtext;

ALTER TABLE sl_node_saves_page CHANGE `PageSaveID` `page_save_id` int(11);
ALTER TABLE sl_node_saves_page CHANGE `PageSaveSession` `page_save_session` int(11);
ALTER TABLE sl_node_saves_page CHANGE `PageSaveNode` `page_save_node` int(11);
ALTER TABLE sl_node_saves_page CHANGE `PageSaveLoopItemID` `page_save_loop_item_id` int(11);

ALTER TABLE sl_search_rec_dump CHANGE `SchRecDmpID` `sch_rec_dmp_id` int(11);
ALTER TABLE sl_search_rec_dump CHANGE `SchRecDmpTreeID` `sch_rec_dmp_tree_id` int(11);
ALTER TABLE sl_search_rec_dump CHANGE `SchRecDmpRecID` `sch_rec_dmp_rec_id` int(11);
ALTER TABLE sl_search_rec_dump CHANGE `SchRecDmpRecDump` `sch_rec_dmp_rec_dump` longtext;

ALTER TABLE sl_sess CHANGE `SessID` `sess_id` int(11);
ALTER TABLE sl_sess CHANGE `SessUserID` `sess_user_id` bigint(20);
ALTER TABLE sl_sess CHANGE `SessTree` `sess_tree` int(11);
ALTER TABLE sl_sess CHANGE `SessCoreID` `sess_core_id` int(11);
ALTER TABLE sl_sess CHANGE `SessIsActive` `sess_is_active` tinyint(1);
ALTER TABLE sl_sess CHANGE `SessCurrNode` `sess_curr_node` int(11);
ALTER TABLE sl_sess CHANGE `SessLoopRootJustLeft` `sess_loop_root_just_left` int(11);
ALTER TABLE sl_sess CHANGE `SessAfterJumpTo` `sess_after_jump_to` int(11);
ALTER TABLE sl_sess CHANGE `SessZoomPref` `sess_zoom_pref` int(11);
ALTER TABLE sl_sess CHANGE `SessIsMobile` `sess_is_mobile` tinyint(1);
ALTER TABLE sl_sess CHANGE `SessBrowser` `sess_browser` varchar(255);
ALTER TABLE sl_sess CHANGE `SessIP` `sess_ip` varchar(255);

ALTER TABLE sl_sess_emojis CHANGE `SessEmoID` `sess_emo_id` int(11);
ALTER TABLE sl_sess_emojis CHANGE `SessEmoUserID` `sess_emo_user_id` bigint(20);
ALTER TABLE sl_sess_emojis CHANGE `SessEmoTreeID` `sess_emo_tree_id` int(11);
ALTER TABLE sl_sess_emojis CHANGE `SessEmoRecID` `sess_emo_rec_id` int(11);
ALTER TABLE sl_sess_emojis CHANGE `SessEmoDefID` `sess_emo_def_id` int(11);

ALTER TABLE sl_sess_loops CHANGE `SessLoopID` `sess_loop_id` int(11);
ALTER TABLE sl_sess_loops CHANGE `SessLoopSessID` `sess_loop_sess_id` int(11);
ALTER TABLE sl_sess_loops CHANGE `SessLoopName` `sess_loop_name` varchar(50);
ALTER TABLE sl_sess_loops CHANGE `SessLoopItemID` `sess_loop_item_id` int(11);

ALTER TABLE sl_sess_page CHANGE `SessPageID` `sess_page_id` int(11);
ALTER TABLE sl_sess_page CHANGE `SessPageSessID` `sess_page_sess_id` int(11);
ALTER TABLE sl_sess_page CHANGE `SessPageURL` `sess_page_url` varchar(255);

ALTER TABLE sl_sess_site CHANGE `SiteSessID` `site_sess_id` int(11);
ALTER TABLE sl_sess_site CHANGE `SiteSessIPaddy` `site_sess_ip_addy` varchar(255);
ALTER TABLE sl_sess_site CHANGE `SiteSessUserID` `site_sess_user_id` bigint(20);
ALTER TABLE sl_sess_site CHANGE `SiteSessIsMobile` `site_sess_is_mobile` tinyint(1);
ALTER TABLE sl_sess_site CHANGE `SiteSessBrowser` `site_sess_browser` varchar(255);
ALTER TABLE sl_sess_site CHANGE `SiteSessZoomPref` `site_sess_zoom_pref` int(11);

ALTER TABLE sl_tables CHANGE `TblID` `tbl_id` int(11);
ALTER TABLE sl_tables CHANGE `TblDatabase` `tbl_database` int(11);
ALTER TABLE sl_tables CHANGE `TblAbbr` `tbl_abbr` varchar(255);
ALTER TABLE sl_tables CHANGE `TblName` `tbl_name` varchar(255);
ALTER TABLE sl_tables CHANGE `TblEng` `tbl_eng` varchar(255);
ALTER TABLE sl_tables CHANGE `TblDesc` `tbl_desc` longtext;
ALTER TABLE sl_tables CHANGE `TblNotes` `tbl_notes` longtext;
ALTER TABLE sl_tables CHANGE `TblType` `tbl_type` varchar(25);
ALTER TABLE sl_tables CHANGE `TblGroup` `tbl_group` varchar(50);
ALTER TABLE sl_tables CHANGE `TblOrd` `tbl_ord` int(11);
ALTER TABLE sl_tables CHANGE `TblOpts` `tbl_opts` int(11);
ALTER TABLE sl_tables CHANGE `TblExtend` `tbl_extend` int(11);
ALTER TABLE sl_tables CHANGE `TblNumFields` `tbl_num_fields` int(11);
ALTER TABLE sl_tables CHANGE `TblNumForeignKeys` `tbl_num_foreign_keys` int(11);
ALTER TABLE sl_tables CHANGE `TblNumForeignIn` `tbl_num_foreign_in` int(11);

ALTER TABLE sl_thread_comments CHANGE `ThrdCmtID` `thrd_cmt_id` int(11);
ALTER TABLE sl_thread_comments CHANGE `ThrdCmtThreadID` `thrd_cmt_thread_id` int(11);
ALTER TABLE sl_thread_comments CHANGE `ThrdCmtUserID` `thrd_cmt_user_id` bigint(20);
ALTER TABLE sl_thread_comments CHANGE `ThrdCmtSessID` `thrd_cmt_sess_id` int(11);
ALTER TABLE sl_thread_comments CHANGE `ThrdCmtReplyTo` `thrd_cmt_reply_to` int(11);
ALTER TABLE sl_thread_comments CHANGE `ThrdCmtReplyRoot` `thrd_cmt_reply_root` int(11);
ALTER TABLE sl_thread_comments CHANGE `ThrdCmtDepth` `thrd_cmt_depth` int(11);
ALTER TABLE sl_thread_comments CHANGE `ThrdCmtTotLikes` `thrd_cmt_tot_likes` int(11);
ALTER TABLE sl_thread_comments CHANGE `ThrdCmtTotDislikes` `thrd_cmt_tot_dislikes` int(11);

ALTER TABLE sl_thread_follows CHANGE `ThrdFlwID` `thrd_flw_id` int(11);
ALTER TABLE sl_thread_follows CHANGE `ThrdFlwCommentID` `thrd_flw_comment_id` int(11);
ALTER TABLE sl_thread_follows CHANGE `ThrdFlwUserID` `thrd_flw_user_id` bigint(20);
ALTER TABLE sl_thread_follows CHANGE `ThrdFlwFollowType` `thrd_flw_follow_type` int(11);

ALTER TABLE sl_thread_likes CHANGE `ThrdLikID` `thrd_lik_id` int(11);
ALTER TABLE sl_thread_likes CHANGE `ThrdLikCommentID` `thrd_lik_comment_id` int(11);
ALTER TABLE sl_thread_likes CHANGE `ThrdLikUserID` `thrd_lik_user_id` bigint(20);
ALTER TABLE sl_thread_likes CHANGE `ThrdLikLike` `thrd_lik_like` int(11);

ALTER TABLE sl_threads CHANGE `ThrdID` `thrd_id` int(11);
ALTER TABLE sl_threads CHANGE `ThrdName` `thrd_name` varchar(255);
ALTER TABLE sl_threads CHANGE `ThrdDiscussTotal` `thrd_discuss_total` int(11);
ALTER TABLE sl_threads CHANGE `ThrdDiscussLast` `thrd_discuss_last` datetime;

ALTER TABLE sl_tokens CHANGE `TokID` `tok_id` int(11);
ALTER TABLE sl_tokens CHANGE `TokType` `tok_type` varchar(20);
ALTER TABLE sl_tokens CHANGE `TokUserID` `tok_user_id` bigint(20);
ALTER TABLE sl_tokens CHANGE `TokTreeID` `tok_tree_id` int(11);
ALTER TABLE sl_tokens CHANGE `TokCoreID` `tok_core_id` int(11);
ALTER TABLE sl_tokens CHANGE `TokTokToken` `tok_tok_token` varchar(100);

ALTER TABLE sl_tree CHANGE `TreeID` `tree_id` int(11);
ALTER TABLE sl_tree CHANGE `TreeDatabase` `tree_database` int(11);
ALTER TABLE sl_tree CHANGE `TreeUser` `tree_user` int(11);
ALTER TABLE sl_tree CHANGE `TreeType` `tree_type` varchar(30);
ALTER TABLE sl_tree CHANGE `TreeName` `tree_name` varchar(255);
ALTER TABLE sl_tree CHANGE `TreeDesc` `tree_desc` longtext;
ALTER TABLE sl_tree CHANGE `TreeSlug` `tree_slug` varchar(255);
ALTER TABLE sl_tree CHANGE `TreeRoot` `tree_root` int(11);
ALTER TABLE sl_tree CHANGE `TreeFirstPage` `tree_first_page` int(11);
ALTER TABLE sl_tree CHANGE `TreeLastPage` `tree_last_page` int(11);
ALTER TABLE sl_tree CHANGE `TreeCoreTable` `tree_core_table` int(11);
ALTER TABLE sl_tree CHANGE `TreeOpts` `tree_opts` int(11);

ALTER TABLE sl_uploads CHANGE `UpID` `up_id` int(11);
ALTER TABLE sl_uploads CHANGE `UpTreeID` `up_tree_id` int(11);
ALTER TABLE sl_uploads CHANGE `UpCoreID` `up_core_id` int(11);
ALTER TABLE sl_uploads CHANGE `UpType` `up_type` int(11);
ALTER TABLE sl_uploads CHANGE `UpPrivacy` `up_privacy` varchar(10);
ALTER TABLE sl_uploads CHANGE `UpTitle` `up_title` varchar(255);
ALTER TABLE sl_uploads CHANGE `UpEvidenceDesc` `up_evidence_desc` text;
ALTER TABLE sl_uploads CHANGE `UpUploadFile` `up_upload_file` varchar(255);
ALTER TABLE sl_uploads CHANGE `UpStoredFile` `up_stored_file` varchar(30);
ALTER TABLE sl_uploads CHANGE `UpVideoLink` `up_video_link` varchar(255);
ALTER TABLE sl_uploads CHANGE `UpVideoDuration` `up_video_duration` int(11);
ALTER TABLE sl_uploads CHANGE `UpNodeID` `up_node_id` int(11);
ALTER TABLE sl_uploads CHANGE `UpLinkFldID` `up_link_fld_id` int(11);
ALTER TABLE sl_uploads CHANGE `UpLinkRecID` `up_link_rec_id` int(11);

ALTER TABLE sl_uploads_time CHANGE `UpTiID` `up_ti_id` int(11);
ALTER TABLE sl_uploads_time CHANGE `UpTiUploadID` `up_ti_upload_id` int(11);
ALTER TABLE sl_uploads_time CHANGE `UpTiTimestamp` `up_ti_timestamp` int(11);
ALTER TABLE sl_uploads_time CHANGE `UpTiDescription` `up_ti_description` varchar(255);
ALTER TABLE sl_uploads_time CHANGE `UpTiLinkFldID` `up_ti_link_fld_id` int(11);
ALTER TABLE sl_uploads_time CHANGE `UpTiLinkRecID` `up_ti_link_rec_id` int(11);

ALTER TABLE sl_users_activity CHANGE `UserActID` `user_act_id` int(11);
ALTER TABLE sl_users_activity CHANGE `UserActUser` `user_act_user` int(11);
ALTER TABLE sl_users_activity CHANGE `UserActCurrPage` `user_act_curr_page` varchar(255);
ALTER TABLE sl_users_activity CHANGE `UserActVal` `user_act_val` longtext;

ALTER TABLE sl_users_roles CHANGE `RoleUserID` `role_user_id` bigint(20);
ALTER TABLE sl_users_roles CHANGE `RoleUserUID` `role_user_uid` int(11);
ALTER TABLE sl_users_roles CHANGE `RoleUserRID` `role_user_rid` int(11);

ALTER TABLE sl_zip_ashrae CHANGE `AshrID` `ashr_id` int(11);
ALTER TABLE sl_zip_ashrae CHANGE `AshrZone` `ashr_zone` varchar(2);
ALTER TABLE sl_zip_ashrae CHANGE `AshrState` `ashr_state` varchar(2);
ALTER TABLE sl_zip_ashrae CHANGE `AshrCounty` `ashr_county` varchar(50);

ALTER TABLE sl_zips CHANGE `ZipID` `zip_id` int(11);
ALTER TABLE sl_zips CHANGE `ZipZip` `zip_zip` varchar(10);
ALTER TABLE sl_zips CHANGE `ZipLat` `zip_lat` double;
ALTER TABLE sl_zips CHANGE `ZipLong` `zip_long` double;
ALTER TABLE sl_zips CHANGE `ZipCity` `zip_city` varchar(100);
ALTER TABLE sl_zips CHANGE `ZipState` `zip_state` varchar(50);
ALTER TABLE sl_zips CHANGE `ZipCounty` `zip_county` varchar(255);
ALTER TABLE sl_zips CHANGE `ZipCountry` `zip_country` varchar(100);

ALTER TABLE sl_addy_geo MODIFY COLUMN `ady_geo_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_bus_rules MODIFY COLUMN `rule_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_caches MODIFY COLUMN `cach_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_campaign_clicks MODIFY COLUMN `cmp_clk_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_campaigns MODIFY COLUMN `camp_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_conditions MODIFY COLUMN `cond_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_conditions_articles MODIFY COLUMN `article_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_conditions_nodes MODIFY COLUMN `cond_node_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_conditions_vals MODIFY COLUMN `cond_val_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_contact MODIFY COLUMN `cont_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_databases MODIFY COLUMN `db_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_data_helpers MODIFY COLUMN `data_help_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_data_loop MODIFY COLUMN `data_loop_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_data_subsets MODIFY COLUMN `data_sub_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_definitions MODIFY COLUMN `def_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_design_tweaks MODIFY COLUMN `twk_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_emailed MODIFY COLUMN `emailed_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_emails MODIFY COLUMN `email_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_fields MODIFY COLUMN `fld_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_images MODIFY COLUMN `img_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_log_actions MODIFY COLUMN `log_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_node MODIFY COLUMN `node_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_node_responses MODIFY COLUMN `node_res_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_node_saves MODIFY COLUMN `node_save_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_node_saves_page MODIFY COLUMN `page_save_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_search_rec_dump MODIFY COLUMN `sch_rec_dmp_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_sess MODIFY COLUMN `sess_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_sess_emojis MODIFY COLUMN `sess_emo_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_sess_loops MODIFY COLUMN `sess_loop_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_sess_page MODIFY COLUMN `sess_page_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_sess_site MODIFY COLUMN `site_sess_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_tables MODIFY COLUMN `tbl_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_thread_comments MODIFY COLUMN `thrd_cmt_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_thread_follows MODIFY COLUMN `thrd_flw_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_thread_likes MODIFY COLUMN `thrd_lik_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_threads MODIFY COLUMN `thrd_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_tokens MODIFY COLUMN `tok_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_tree MODIFY COLUMN `tree_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_uploads MODIFY COLUMN `up_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_uploads_time MODIFY COLUMN `up_ti_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_users_activity MODIFY COLUMN `user_act_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_users_roles MODIFY COLUMN `role_user_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_zip_ashrae MODIFY COLUMN `ashr_id` int(10) AUTO_INCREMENT;
ALTER TABLE sl_zips MODIFY COLUMN `zip_id` int(10) AUTO_INCREMENT;




CREATE INDEX sl_bus_rules_rule_databasex ON sl_bus_rules(rule_database);

CREATE INDEX sl_caches_cach_rec_idx ON sl_caches(cach_rec_id);
CREATE INDEX sl_caches_cach_keyx ON sl_caches(cach_key);

CREATE INDEX sl_campaign_clicks_cmp_clk_campaign_idx ON sl_campaign_clicks(cmp_clk_campaign_id);

CREATE INDEX sl_conditions_cond_databasex ON sl_conditions(cond_database);
CREATE INDEX sl_conditions_articles_article_cond_idx ON sl_conditions_articles(article_cond_id);
CREATE INDEX sl_conditions_nodes_cond_node_cond_idx ON sl_conditions_nodes(cond_node_cond_id);
CREATE INDEX sl_conditions_vals_cond_val_cond_idx ON sl_conditions_vals(cond_val_cond_id);

CREATE INDEX sl_data_helpers_data_help_treex ON sl_data_helpers(data_help_tree);
CREATE INDEX sl_data_links_data_link_treex ON sl_data_links(data_link_tree);
CREATE INDEX sl_data_loop_data_loop_treex ON sl_data_loop(data_loop_tree);
CREATE INDEX sl_data_subsets_data_sub_treex ON sl_data_subsets(data_sub_tree);

CREATE INDEX sl_definitions_def_databasex ON sl_definitions(def_database);

CREATE INDEX sl_emails_email_treex ON sl_emails(email_tree);

CREATE INDEX sl_fields_fld_databasex ON sl_fields(fld_database);
CREATE INDEX sl_fields_fld_tablex ON sl_fields(fld_table);

CREATE INDEX sl_node_node_tree_idx ON sl_node(node_tree);
CREATE INDEX sl_node_node_parent_idx ON sl_node(node_parent_id);
CREATE INDEX sl_node_responses_node_res_nodex ON sl_node_responses(node_res_node);
CREATE INDEX sl_node_saves_node_save_sessionx ON sl_node_saves(node_save_session);
CREATE INDEX sl_node_saves_node_save_nodex ON sl_node_saves(node_save_node);
CREATE INDEX sl_node_saves_page_save_sessionx ON sl_node_saves_page(page_save_session);
CREATE INDEX sl_node_saves_page_save_nodex ON sl_node_saves_page(page_save_node);

CREATE INDEX sl_search_rec_dump_sch_rec_dmp_tree_idx ON sl_search_rec_dump(sch_rec_dmp_tree_id);
CREATE INDEX sl_search_rec_dump_sch_rec_dmp_rec_idx ON sl_search_rec_dump(sch_rec_dmp_rec_id);

CREATE INDEX sl_sess_sess_is_activex ON sl_sess(sess_is_active);
CREATE INDEX sl_sess_emojis_sess_emo_user_idx ON sl_sess_emojis(sess_emo_user_id);
CREATE INDEX sl_sess_emojis_sess_emo_tree_idx ON sl_sess_emojis(sess_emo_tree_id);
CREATE INDEX sl_sess_emojis_sess_emo_rec_idx ON sl_sess_emojis(sess_emo_rec_id);
CREATE INDEX sl_sess_loops_sess_loop_sess_idx ON sl_sess_loops(sess_loop_sess_id);
CREATE INDEX sl_sess_site_site_sess_user_idx ON sl_sess_site(site_sess_user_id);

CREATE INDEX sl_tables_tbl_databasex ON sl_tables(tbl_database);

CREATE INDEX sl_thread_comments_thrd_cmt_thread_idx ON sl_thread_comments(thrd_cmt_thread_id);
CREATE INDEX sl_thread_follows_thrd_flw_comment_idx ON sl_thread_follows(thrd_flw_comment_id);
CREATE INDEX sl_thread_likes_thrd_lik_comment_idx ON sl_thread_likes(thrd_lik_comment_id);
CREATE INDEX sl_threads_thrd_namex ON sl_threads(thrd_name);

CREATE INDEX sl_tree_tree_databasex ON sl_tree(tree_database);
CREATE INDEX sl_tree_tree_typex ON sl_tree(tree_type);
CREATE INDEX sl_tree_tree_slugx ON sl_tree(tree_slug);

CREATE INDEX sl_uploads_up_tree_idx ON sl_uploads(up_tree_id);

CREATE INDEX sl_users_activity_user_act_userx ON sl_users_activity(user_act_user);
CREATE INDEX sl_users_roles_role_user_uidx ON sl_users_roles(role_user_uid);
CREATE INDEX sl_users_roles_role_user_ridx ON sl_users_roles(role_user_rid);





