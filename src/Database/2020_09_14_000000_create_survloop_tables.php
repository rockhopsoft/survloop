<?php 
// generated from /resources/views/vendor/survloop/admin/db/export-laravel-gen-migration.blade.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSurvloopTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
    	//DB::statement('SET SESSION sql_require_primary_key=0');
    	Schema::create('sl_databases', function(Blueprint $table)
		{
			$table->increments('db_id');
			$table->integer('db_user')->unsigned()->nullable();
			$table->string('db_prefix', 25)->nullable();
			$table->string('db_name')->nullable();
			$table->longText('db_desc')->nullable();
			$table->longText('db_mission')->nullable();
			$table->integer('db_opts')->default('1')->nullable();
			$table->integer('db_tables')->default('0')->nullable();
			$table->integer('db_fields')->default('0')->nullable();
			$table->timestamps();
		});
		Schema::create('sl_tables', function(Blueprint $table)
		{
			$table->increments('tbl_id');
			$table->integer('tbl_database')->unsigned()->nullable();
			$table->string('tbl_abbr')->nullable();
			$table->string('tbl_name')->nullable();
			$table->string('tbl_eng')->nullable();
			$table->longText('tbl_desc')->nullable();
			$table->longText('tbl_notes')->nullable();
			$table->string('tbl_type', 25)->default('Data')->nullable();
			$table->string('tbl_group', 50)->nullable();
			$table->integer('tbl_ord')->default('0')->nullable();
			$table->integer('tbl_opts')->default('1')->nullable();
			$table->integer('tbl_extend')->unsigned()->default('0')->nullable();
			$table->integer('tbl_num_fields')->default('0')->nullable();
			$table->integer('tbl_num_foreign_keys')->default('0')->nullable();
			$table->integer('tbl_num_foreign_in')->default('0')->nullable();
			$table->timestamps();
			$table->index('tbl_database');
		});
		Schema::create('sl_fields', function(Blueprint $table)
		{
			$table->increments('fld_id');
			$table->integer('fld_database')->unsigned()->nullable();
			$table->integer('fld_table')->unsigned()->nullable();
			$table->integer('fld_ord')->default('0')->nullable();
			$table->string('fld_spec_type', 10)->default('Unique')->nullable();
			$table->integer('fld_spec_source')->default('-3')->nullable();
			$table->string('fld_name')->nullable();
			$table->string('fld_eng')->nullable();
			$table->string('fld_alias')->nullable();
			$table->longText('fld_desc')->nullable();
			$table->longText('fld_notes')->nullable();
			$table->integer('fld_foreign_table')->default('-3')->nullable();
			$table->string('fld_foreign_min', 11)->default('1')->nullable();
			$table->string('fld_foreign_max', 11)->default('1')->nullable();
			$table->string('fld_foreign2_min', 11)->default('1')->nullable();
			$table->string('fld_foreign2_max', 11)->default('1')->nullable();
			$table->longText('fld_values')->nullable();
			$table->string('fld_default')->nullable();
			$table->boolean('fld_is_index')->default(0)->nullable();
			$table->string('fld_type', 25)->default('VARCHAR')->nullable();
			$table->string('fld_data_type', 20)->default('Alphanumeric')->nullable();
			$table->integer('fld_data_length')->nullable();
			$table->integer('fld_data_decimals')->default('0')->nullable();
			$table->string('fld_char_support')->default(',Letters,Numbers,Keyboard,Special,')->nullable();
			$table->string('fld_input_mask')->nullable();
			$table->string('fld_display_format')->nullable();
			$table->string('fld_key_type')->default(',Non,')->nullable();
			$table->string('fld_key_struct', 10)->nullable();
			$table->string('fld_edit_rule', 10)->default('LateAllow')->nullable();
			$table->boolean('fld_unique')->default(0)->nullable();
			$table->boolean('fld_null_support')->default(1)->nullable();
			$table->string('fld_values_entered_by', 10)->default('User')->nullable();
			$table->boolean('fld_required')->default(0)->nullable();
			$table->integer('fld_compare_same')->default('1')->nullable();
			$table->integer('fld_compare_other')->default('1')->nullable();
			$table->integer('fld_compare_value')->default('1')->nullable();
			$table->integer('fld_operate_same')->default('1')->nullable();
			$table->integer('fld_operate_other')->default('1')->nullable();
			$table->integer('fld_operate_value')->default('1')->nullable();
			$table->integer('fld_opts')->default('1')->nullable();
			$table->timestamps();
			$table->index('fld_database');
			$table->index('fld_table');
		});
		Schema::create('sl_definitions', function(Blueprint $table)
		{
			$table->increments('def_id');
			$table->integer('def_database')->unsigned()->nullable();
			$table->string('def_set', 50)->default('Value Ranges')->nullable();
			$table->string('def_subset', 50)->nullable();
			$table->integer('def_order')->default('0')->nullable();
			$table->boolean('def_is_active')->default(1)->nullable();
			$table->string('def_value')->nullable();
			$table->longText('def_description')->nullable();
			$table->timestamps();
			$table->index('def_database');
			$table->index('def_set');
		});
		Schema::create('sl_bus_rules', function(Blueprint $table)
		{
			$table->increments('rule_id');
			$table->integer('rule_database')->unsigned()->nullable();
			$table->longText('rule_statement')->nullable();
			$table->longText('rule_constraint')->nullable();
			$table->string('rule_tables')->default(',')->nullable();
			$table->string('rule_fields')->default(',')->nullable();
			$table->boolean('rule_is_app_orient')->default(1)->nullable();
			$table->boolean('rule_is_relation')->default(1)->nullable();
			$table->string('rule_test_on', 10)->default('Insert')->nullable();
			$table->integer('rule_phys')->default('1')->nullable();
			$table->integer('rule_logic')->default('1')->nullable();
			$table->integer('rule_rel')->default('1')->nullable();
			$table->longText('rule_action')->nullable();
			$table->timestamps();
			$table->index('rule_database');
		});
		Schema::create('sl_tree', function(Blueprint $table)
		{
			$table->increments('tree_id');
			$table->integer('tree_database')->unsigned()->nullable();
			$table->integer('tree_user')->unsigned()->nullable();
			$table->string('tree_type', 30)->default('Primary Public')->nullable();
			$table->string('tree_name')->nullable();
			$table->longText('tree_desc')->nullable();
			$table->string('tree_slug')->nullable();
			$table->integer('tree_root')->unsigned()->nullable();
			$table->integer('tree_first_page')->nullable();
			$table->integer('tree_last_page')->nullable();
			$table->integer('tree_core_table')->nullable();
			$table->integer('tree_opts')->default('1')->nullable();
			$table->timestamps();
			$table->index('tree_database');
			$table->index('tree_type');
			$table->index('tree_slug');
		});
		Schema::create('sl_node', function(Blueprint $table)
		{
			$table->increments('node_id');
			$table->integer('node_tree')->unsigned()->nullable();
			$table->integer('node_parent_id')->default('-3')->nullable();
			$table->integer('node_parent_order')->default('0')->nullable();
			$table->string('node_type', 25)->nullable();
			$table->longText('node_prompt_text')->nullable();
			$table->longText('node_prompt_notes')->nullable();
			$table->longText('node_prompt_after')->nullable();
			$table->longText('node_internal_notes')->nullable();
			$table->string('node_response_set', 50)->nullable();
			$table->string('node_default')->nullable();
			$table->string('node_data_branch', 100)->nullable();
			$table->string('node_data_store', 100)->nullable();
			$table->string('node_text_suggest', 100)->nullable();
			$table->integer('node_char_limit')->default('0')->nullable();
			$table->integer('node_likes')->default('0')->nullable();
			$table->integer('node_dislikes')->default('0')->nullable();
			$table->integer('node_opts')->default('1')->nullable();
			$table->timestamps();
			$table->index('node_tree');
			$table->index('node_parent_id');
		});
		Schema::create('sl_node_responses', function(Blueprint $table)
		{
			$table->increments('node_res_id');
			$table->integer('node_res_node')->unsigned()->nullable();
			$table->integer('node_res_ord')->default('0')->nullable();
			$table->longText('node_res_eng')->nullable();
			$table->string('node_res_value')->nullable();
			$table->integer('node_res_show_kids')->default('0')->nullable();
			$table->integer('node_res_mut_ex')->default('0')->nullable();
			$table->timestamps();
			$table->index('node_res_node');
		});
		Schema::create('sl_conditions', function(Blueprint $table)
		{
			$table->increments('cond_id');
			$table->integer('cond_database')->unsigned()->nullable();
			$table->string('cond_tag', 100)->nullable();
			$table->longText('cond_desc')->nullable();
			$table->string('cond_operator', 50)->default('{')->nullable();
			$table->string('cond_oper_deet', 100)->nullable();
			$table->integer('cond_field')->unsigned()->nullable();
			$table->integer('cond_table')->unsigned()->nullable();
			$table->integer('cond_loop')->unsigned()->nullable();
			$table->integer('cond_opts')->default('1')->nullable();
			$table->timestamps();
			$table->index('cond_database');
			$table->index('cond_tag');
		});
		Schema::create('sl_conditions_vals', function(Blueprint $table)
		{
			$table->increments('cond_val_id');
			$table->integer('cond_val_cond_id')->unsigned()->nullable();
			$table->string('cond_val_value')->nullable();
			$table->timestamps();
			$table->index('cond_val_cond_id');
		});
		Schema::create('sl_conditions_nodes', function(Blueprint $table)
		{
			$table->increments('cond_node_id');
			$table->integer('cond_node_cond_id')->unsigned()->nullable();
			$table->integer('cond_node_node_id')->nullable();
			$table->integer('cond_node_loop_id')->nullable();
			$table->timestamps();
			$table->index('cond_node_cond_id');
		});
		Schema::create('sl_conditions_articles', function(Blueprint $table)
		{
			$table->increments('article_id');
			$table->integer('article_cond_id')->unsigned()->nullable();
			$table->string('article_url')->nullable();
			$table->string('article_title')->nullable();
			$table->timestamps();
			$table->index('article_cond_id');
		});
		Schema::create('sl_data_loop', function(Blueprint $table)
		{
			$table->increments('data_loop_id');
			$table->integer('data_loop_tree')->unsigned()->nullable();
			$table->integer('data_loop_root')->unsigned()->nullable();
			$table->string('data_loop_plural', 50)->nullable();
			$table->string('data_loop_singular', 50)->nullable();
			$table->string('data_loop_table', 100)->nullable();
			$table->string('data_loop_sort_fld', 100)->nullable();
			$table->string('data_loop_done_fld', 100)->nullable();
			$table->integer('data_loop_max_limit')->default('0')->nullable();
			$table->integer('data_loop_warn_limit')->default('0')->nullable();
			$table->integer('data_loop_min_limit')->default('0')->nullable();
			$table->boolean('data_loop_is_step')->default(0)->nullable();
			$table->boolean('data_loop_auto_gen')->default(1)->nullable();
			$table->timestamps();
			$table->index('data_loop_tree');
			$table->index('data_loop_root');
		});
		Schema::create('sl_data_subsets', function(Blueprint $table)
		{
			$table->increments('data_sub_id');
			$table->integer('data_sub_tree')->unsigned()->nullable();
			$table->string('data_sub_tbl', 100)->nullable();
			$table->string('data_sub_tbl_lnk', 100)->nullable();
			$table->string('data_sub_sub_tbl', 50)->nullable();
			$table->string('data_sub_sub_lnk', 100)->nullable();
			$table->boolean('data_sub_auto_gen')->default(0)->nullable();
			$table->timestamps();
			$table->index('data_sub_tree');
		});
		Schema::create('sl_data_helpers', function(Blueprint $table)
		{
			$table->increments('data_help_id');
			$table->integer('data_help_tree')->unsigned()->nullable();
			$table->string('data_help_parent_table', 50)->nullable();
			$table->string('data_help_table', 50)->nullable();
			$table->string('data_help_key_field', 50)->nullable();
			$table->string('data_help_value_field', 50)->nullable();
			$table->timestamps();
			$table->index('data_help_tree');
		});
		Schema::create('sl_data_links', function(Blueprint $table)
		{
			$table->increments('data_link_id');
			$table->integer('data_link_tree')->unsigned()->nullable();
			$table->integer('data_link_table')->unsigned()->nullable();
			$table->timestamps();
			$table->index('data_link_tree');
		});
		Schema::create('sl_images', function(Blueprint $table)
		{
			$table->increments('img_id');
			$table->integer('img_database_id')->unsigned()->nullable();
			$table->integer('img_user_id')->unsigned()->nullable();
			$table->string('img_file_orig')->nullable();
			$table->string('img_file_loc')->nullable();
			$table->string('img_full_filename')->nullable();
			$table->string('img_title')->nullable();
			$table->string('img_credit')->nullable();
			$table->string('img_credit_url')->nullable();
			$table->integer('img_node_id')->nullable();
			$table->string('img_type', 10)->nullable();
			$table->integer('img_file_size')->nullable();
			$table->integer('img_width')->nullable();
			$table->integer('img_height')->nullable();
			$table->timestamps();
			$table->index('img_database_id');
		});
		Schema::create('sl_uploads', function(Blueprint $table)
		{
			$table->increments('up_id');
			$table->integer('up_tree_id')->unsigned()->nullable();
			$table->integer('up_node_id')->nullable();
			$table->integer('up_core_id')->nullable();
			$table->integer('up_type')->unsigned()->nullable();
			$table->string('up_privacy', 10)->nullable();
			$table->string('up_title')->nullable();
			$table->longText('up_desc')->nullable();
			$table->string('up_upload_file')->nullable();
			$table->string('up_stored_file')->nullable();
			$table->string('up_video_link')->nullable();
			$table->integer('up_video_duration')->nullable();
			$table->integer('up_link_fld_id')->unsigned()->nullable();
			$table->integer('up_link_rec_id')->nullable();
			$table->timestamps();
			$table->index('up_tree_id');
			$table->index('up_node_id');
			$table->index('up_core_id');
		});
		Schema::create('sl_search_rec_dump', function(Blueprint $table)
		{
			$table->increments('sch_rec_dmp_id');
			$table->integer('sch_rec_dmp_tree_id')->unsigned()->nullable();
			$table->integer('sch_rec_dmp_rec_id')->nullable();
			$table->longText('sch_rec_dmp_dump')->nullable();
			$table->timestamps();
			$table->index('sch_rec_dmp_tree_id');
			$table->index('sch_rec_dmp_rec_id');
		});
		Schema::create('sl_design_tweaks', function(Blueprint $table)
		{
			$table->increments('twk_id');
			$table->string('twk_version_ab')->nullable();
			$table->integer('twk_submission_progress')->unsigned()->nullable();
			$table->string('twk_ip_addy')->nullable();
			$table->string('twk_tree_version')->nullable();
			$table->string('twk_unique_str')->nullable();
			$table->integer('twk_user_id')->unsigned()->nullable();
			$table->string('twk_is_mobile')->nullable();
			$table->timestamps();
		});
		Schema::create('sl_sess', function(Blueprint $table)
		{
			$table->increments('sess_id');
			$table->integer('sess_user_id')->unsigned()->nullable();
			$table->integer('sess_tree')->unsigned()->nullable();
			$table->integer('sess_core_id')->unsigned()->nullable();
			$table->boolean('sess_is_active')->nullable();
			$table->integer('sess_curr_node')->unsigned()->nullable();
			$table->integer('sess_loop_root_just_left')->unsigned()->nullable();
			$table->integer('sess_after_jump_to')->unsigned()->nullable();
			$table->boolean('sess_is_mobile')->nullable();
			$table->string('sess_browser', 255)->nullable();
			$table->string('sess_ip')->nullable();
			$table->timestamps();
			$table->index('sess_user_id');
			$table->index('sess_tree');
			$table->index('sess_core_id');
			$table->index('sess_is_active');
		});
		Schema::create('sl_sess_loops', function(Blueprint $table)
		{
			$table->increments('sess_loop_id');
			$table->integer('sess_loop_sess_id')->unsigned()->nullable();
			$table->string('sess_loop_name', 50)->nullable();
			$table->integer('sess_loop_item_id')->nullable();
			$table->timestamps();
			$table->index('sess_loop_sess_id');
		});
		Schema::create('sl_node_saves_page', function(Blueprint $table)
		{
			$table->increments('page_save_id');
			$table->integer('page_save_session')->unsigned()->nullable();
			$table->integer('page_save_node')->unsigned()->nullable();
			$table->integer('page_save_loop_item_id')->nullable();
			$table->timestamps();
			$table->index('page_save_session');
			$table->index('page_save_node');
		});
		Schema::create('sl_node_saves', function(Blueprint $table)
		{
			$table->increments('node_save_id');
			$table->integer('node_save_session')->unsigned()->nullable();
			$table->integer('node_save_loop_item_id')->nullable();
			$table->integer('node_save_node')->unsigned()->nullable();
			$table->string('node_save_version_ab')->nullable();
			$table->string('node_save_tbl_fld', 100)->nullable();
			$table->longText('node_save_new_val')->nullable();
			$table->timestamps();
			$table->index('node_save_session');
			$table->index('node_save_node');
		});
		Schema::create('sl_sess_emojis', function(Blueprint $table)
		{
			$table->increments('sess_emo_id');
			$table->integer('sess_emo_user_id')->unsigned()->nullable();
			$table->integer('sess_emo_tree_id')->unsigned()->nullable();
			$table->integer('sess_emo_rec_id')->nullable();
			$table->integer('sess_emo_def_id')->unsigned()->nullable();
			$table->timestamps();
			$table->index('sess_emo_user_id');
			$table->index('sess_emo_tree_id');
			$table->index('sess_emo_rec_id');
		});
		Schema::create('sl_sess_site', function(Blueprint $table)
		{
			$table->increments('site_sess_id');
			$table->string('site_sess_ip_addy')->nullable();
			$table->longText('site_sess_user_id')->nullable();
			$table->boolean('site_sess_is_mobile')->nullable();
			$table->string('site_sess_browser', 255)->nullable();
			$table->integer('site_sess_zoom_pref')->nullable();
			$table->timestamps();
			$table->index('site_sess_ip_addy');
		});
		Schema::create('sl_campaigns', function(Blueprint $table)
		{
			$table->increments('camp_id');
			$table->string('camp_name')->nullable();
			$table->timestamps();
		});
		Schema::create('sl_campaign_clicks', function(Blueprint $table)
		{
			$table->increments('camp_clk_id');
			$table->integer('camp_clk_campaign_id')->unsigned()->nullable();
			$table->longText('camp_clk_from_url')->nullable();
			$table->longText('camp_clk_to_url')->nullable();
			$table->timestamps();
		});
		Schema::create('sl_sess_page', function(Blueprint $table)
		{
			$table->increments('sess_page_id');
			$table->integer('sess_page_sess_id')->unsigned()->nullable();
			$table->string('sess_page_url')->nullable();
			$table->timestamps();
			$table->index('sess_page_sess_id');
		});
		Schema::create('sl_threads', function(Blueprint $table)
		{
			$table->increments('thrd_id');
			$table->string('thrd_name')->nullable();
			$table->integer('thrd_discuss_total')->nullable();
			$table->dateTime('thrd_discuss_last')->nullable();
			$table->timestamps();
		});
		Schema::create('sl_thread_comments', function(Blueprint $table)
		{
			$table->increments('thrd_cmt_id');
			$table->integer('thrd_cmt_thread_id')->unsigned()->nullable();
			$table->integer('thrd_cmt_user_id')->unsigned()->nullable();
			$table->integer('thrd_cmt_sess_id')->unsigned()->nullable();
			$table->integer('thrd_cmt_reply_to')->unsigned()->default('0')->nullable();
			$table->integer('thrd_cmt_reply_root')->unsigned()->default('0')->nullable();
			$table->integer('thrd_cmt_depth')->default('0')->nullable();
			$table->integer('thrd_cmt_tot_likes')->default('0')->nullable();
			$table->integer('thrd_cmt_tot_dislikes')->default('0')->nullable();
			$table->timestamps();
			$table->index('thrd_cmt_thread_id');
			$table->index('thrd_cmt_user_id');
		});
		Schema::create('sl_thread_likes', function(Blueprint $table)
		{
			$table->increments('thrd_lik_id');
			$table->integer('thrd_lik_comment_id')->unsigned()->nullable();
			$table->integer('thrd_lik_user_id')->unsigned()->nullable();
			$table->integer('thrd_lik_like')->default('0')->nullable();
			$table->timestamps();
			$table->index('thrd_lik_comment_id');
			$table->index('thrd_lik_user_id');
		});
		Schema::create('sl_thread_follows', function(Blueprint $table)
		{
			$table->increments('thrd_flw_id');
			$table->integer('thrd_flw_comment_id')->unsigned()->nullable();
			$table->integer('thrd_flw_user_id')->unsigned()->nullable();
			$table->integer('thrd_flw_follow_type')->default('1')->nullable();
			$table->timestamps();
			$table->index('thrd_flw_comment_id');
			$table->index('thrd_flw_user_id');
		});
		Schema::create('sl_users_roles', function(Blueprint $table)
		{
			$table->increments('role_user_id');
			$table->integer('role_user_uid')->unsigned()->nullable();
			$table->integer('role_user_rid')->nullable();
			$table->timestamps();
			$table->index('role_user_uid');
			$table->index('role_user_rid');
		});
		Schema::create('sl_emails', function(Blueprint $table)
		{
			$table->increments('email_id');
			$table->integer('email_tree')->unsigned()->nullable();
			$table->string('email_type')->nullable();
			$table->string('email_name')->nullable();
			$table->longText('email_subject')->nullable();
			$table->longText('email_body')->nullable();
			$table->integer('email_opts')->default('1')->nullable();
			$table->integer('email_tot_sent')->default('0')->nullable();
			$table->timestamps();
			$table->index('email_tree');
			$table->index('email_type');
		});
		Schema::create('sl_emailed', function(Blueprint $table)
		{
			$table->increments('emailed_id');
			$table->integer('emailed_tree')->unsigned()->nullable();
			$table->integer('emailed_rec_id')->nullable();
			$table->integer('emailed_email_id')->unsigned()->nullable();
			$table->string('emailed_to')->nullable();
			$table->integer('emailed_to_user')->unsigned()->nullable();
			$table->integer('emailed_from_user')->unsigned()->nullable();
			$table->string('emailed_subject')->nullable();
			$table->longText('emailed_body')->nullable();
			$table->integer('emailed_opts')->default('1')->nullable();
			$table->timestamps();
			$table->index('emailed_tree');
			$table->index('emailed_rec_id');
			$table->index('emailed_email_id');
		});
		Schema::create('sl_tokens', function(Blueprint $table)
		{
			$table->increments('tok_id');
			$table->string('tok_type', 20)->nullable();
			$table->integer('tok_user_id')->unsigned()->nullable();
			$table->integer('tok_tree_id')->unsigned()->nullable();
			$table->integer('tok_core_id')->nullable();
			$table->string('tok_tok_token', 255)->nullable();
			$table->timestamps();
			$table->index('tok_type');
			$table->index('tok_user_id');
			$table->index('tok_tree_id');
		});
		Schema::create('sl_contact', function(Blueprint $table)
		{
			$table->increments('cont_id');
			$table->string('cont_type')->nullable();
			$table->string('cont_flag')->default('Unread')->nullable();
			$table->string('cont_email')->nullable();
			$table->string('cont_subject')->nullable();
			$table->longText('cont_body')->nullable();
			$table->timestamps();
		});
		Schema::create('sl_users_activity', function(Blueprint $table)
		{
			$table->increments('user_act_id');
			$table->integer('user_act_user')->unsigned()->nullable();
			$table->string('user_act_curr_page')->nullable();
			$table->longText('user_act_val')->nullable();
			$table->timestamps();
			$table->index('user_act_user');
		});
		Schema::create('sl_log_actions', function(Blueprint $table)
		{
			$table->increments('log_id');
			$table->integer('log_user')->unsigned()->nullable();
			$table->integer('log_database')->unsigned()->nullable();
			$table->integer('log_table')->unsigned()->nullable();
			$table->integer('log_field')->unsigned()->nullable();
			$table->string('log_action', 20)->nullable();
			$table->string('log_old_name')->nullable();
			$table->string('log_new_name')->nullable();
			$table->timestamps();
			$table->index('log_user');
			$table->index('log_action');
		});
		Schema::create('sl_zips', function(Blueprint $table)
		{
			$table->increments('zip_id');
			$table->string('zip_zip', 10)->nullable();
			$table->string('zip_lat')->nullable();
			$table->string('zip_long')->nullable();
			$table->string('zip_city', 100)->nullable();
			$table->string('zip_state')->nullable();
			$table->string('zip_county')->nullable();
			$table->string('zip_country', 100)->nullable();
			$table->timestamps();
			$table->index('zip_zip');
			$table->index('zip_city');
			$table->index('zip_state');
			$table->index('zip_county');
		});
		Schema::create('sl_addy_geo', function(Blueprint $table)
		{
			$table->increments('ady_geo_id');
			$table->string('ady_geo_address')->nullable();
			$table->string('ady_geo_lat')->nullable();
			$table->string('ady_geo_long')->nullable();
			$table->timestamps();
			$table->index('ady_geo_address');
		});
		Schema::create('sl_caches', function(Blueprint $table)
		{
			$table->increments('cach_id');
			$table->string('cach_type', 12)->nullable();
			$table->integer('cach_tree_id')->unsigned()->nullable();
			$table->integer('cach_rec_id')->nullable();
			$table->longText('cach_key')->nullable();
			$table->longText('cach_value')->nullable();
			$table->longText('cach_css')->nullable();
			$table->longText('cach_js')->nullable();
			$table->timestamps();
			$table->index('cach_type');
			$table->index('cach_tree_id');
			$table->index('cach_rec_id');
		});
	
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
    	Schema::drop('sl_databases');
		Schema::drop('sl_tables');
		Schema::drop('sl_fields');
		Schema::drop('sl_definitions');
		Schema::drop('sl_bus_rules');
		Schema::drop('sl_tree');
		Schema::drop('sl_node');
		Schema::drop('sl_node_responses');
		Schema::drop('sl_conditions');
		Schema::drop('sl_conditions_vals');
		Schema::drop('sl_conditions_nodes');
		Schema::drop('sl_conditions_articles');
		Schema::drop('sl_data_loop');
		Schema::drop('sl_data_subsets');
		Schema::drop('sl_data_helpers');
		Schema::drop('sl_data_links');
		Schema::drop('sl_images');
		Schema::drop('sl_uploads');
		Schema::drop('sl_search_rec_dump');
		Schema::drop('sl_design_tweaks');
		Schema::drop('sl_sess');
		Schema::drop('sl_sess_loops');
		Schema::drop('sl_node_saves_page');
		Schema::drop('sl_node_saves');
		Schema::drop('sl_sess_emojis');
		Schema::drop('sl_sess_site');
		Schema::drop('sl_campaigns');
		Schema::drop('sl_campaign_clicks');
		Schema::drop('sl_sess_page');
		Schema::drop('sl_threads');
		Schema::drop('sl_thread_comments');
		Schema::drop('sl_thread_likes');
		Schema::drop('sl_thread_follows');
		Schema::drop('sl_users_roles');
		Schema::drop('sl_emails');
		Schema::drop('sl_emailed');
		Schema::drop('sl_tokens');
		Schema::drop('sl_contact');
		Schema::drop('sl_users_activity');
		Schema::drop('sl_log_actions');
		Schema::drop('sl_zips');
		Schema::drop('sl_addy_geo');
		Schema::drop('sl_caches');
	
    }
}
