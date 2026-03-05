/*M!999999\- enable the sandbox mode */
-- MariaDB dump 10.19-12.0.2-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: cafevdb_cloud_connector
-- ------------------------------------------------------
-- Server version	12.0.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Temporary table structure for view `NextcloudGroupView`
--

DROP TABLE IF EXISTS `NextcloudGroupView`;
/*!50001 DROP VIEW IF EXISTS `NextcloudGroupView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `NextcloudGroupView` AS SELECT
 1 AS `gid`,
  1 AS `display_name`,
  1 AS `is_admin` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `NextcloudUserGroupView`
--

DROP TABLE IF EXISTS `NextcloudUserGroupView`;
/*!50001 DROP VIEW IF EXISTS `NextcloudUserGroupView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `NextcloudUserGroupView` AS SELECT
 1 AS `uid`,
  1 AS `gid` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `NextcloudUserView`
--

DROP TABLE IF EXISTS `NextcloudUserView`;
/*!50001 DROP VIEW IF EXISTS `NextcloudUserView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `NextcloudUserView` AS SELECT
 1 AS `uid`,
  1 AS `password`,
  1 AS `name`,
  1 AS `email`,
  1 AS `quota`,
  1 AS `home`,
  1 AS `inactive`,
  1 AS `disabled`,
  1 AS `avatar`,
  1 AS `salt` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedCompositePaymentsView`
--

DROP TABLE IF EXISTS `PersonalizedCompositePaymentsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedCompositePaymentsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedCompositePaymentsView` AS SELECT
 1 AS `amount`,
  1 AS `date_of_receipt`,
  1 AS `subject`,
  1 AS `notification_message_id`,
  1 AS `id`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `sepa_transaction_id`,
  1 AS `musician_id`,
  1 AS `bank_account_sequence`,
  1 AS `debit_mandate_sequence`,
  1 AS `pre_notification_message_id`,
  1 AS `project_id`,
  1 AS `supporting_document_id`,
  1 AS `balance_documents_folder_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedDatabaseStorageDirEntriesView`
--

DROP TABLE IF EXISTS `PersonalizedDatabaseStorageDirEntriesView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedDatabaseStorageDirEntriesView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedDatabaseStorageDirEntriesView` AS SELECT
 1 AS `name`,
  1 AS `id`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `parent_id`,
  1 AS `type`,
  1 AS `file_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedEncryptedFileOwnersView`
--

DROP TABLE IF EXISTS `PersonalizedEncryptedFileOwnersView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedEncryptedFileOwnersView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedEncryptedFileOwnersView` AS SELECT
 1 AS `musician_id`,
  1 AS `encrypted_file_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedFileDataView`
--

DROP TABLE IF EXISTS `PersonalizedFileDataView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedFileDataView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedFileDataView` AS SELECT
 1 AS `data_hash`,
  1 AS `data`,
  1 AS `file_id`,
  1 AS `type` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedFilesView`
--

DROP TABLE IF EXISTS `PersonalizedFilesView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedFilesView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedFilesView` AS SELECT
 1 AS `file_name`,
  1 AS `mime_type`,
  1 AS `size`,
  1 AS `data_hash`,
  1 AS `updated`,
  1 AS `id`,
  1 AS `created`,
  1 AS `type`,
  1 AS `width`,
  1 AS `height` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedGeoContinentsView`
--

DROP TABLE IF EXISTS `PersonalizedGeoContinentsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedGeoContinentsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedGeoContinentsView` AS SELECT
 1 AS `code`,
  1 AS `target`,
  1 AS `l10n_name` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedGeoCountriesView`
--

DROP TABLE IF EXISTS `PersonalizedGeoCountriesView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedGeoCountriesView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedGeoCountriesView` AS SELECT
 1 AS `iso`,
  1 AS `target`,
  1 AS `l10n_name`,
  1 AS `continent_code` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedGeoPostalCodeTranslationsView`
--

DROP TABLE IF EXISTS `PersonalizedGeoPostalCodeTranslationsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedGeoPostalCodeTranslationsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedGeoPostalCodeTranslationsView` AS SELECT
 1 AS `target`,
  1 AS `translation`,
  1 AS `geo_postal_code_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedGeoPostalCodesView`
--

DROP TABLE IF EXISTS `PersonalizedGeoPostalCodesView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedGeoPostalCodesView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedGeoPostalCodesView` AS SELECT
 1 AS `country`,
  1 AS `state_province`,
  1 AS `postal_code`,
  1 AS `name`,
  1 AS `latitude`,
  1 AS `longitude`,
  1 AS `id`,
  1 AS `created`,
  1 AS `updated` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedInstrumentFamiliesView`
--

DROP TABLE IF EXISTS `PersonalizedInstrumentFamiliesView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedInstrumentFamiliesView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedInstrumentFamiliesView` AS SELECT
 1 AS `family`,
  1 AS `id`,
  1 AS `deleted` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedInstrumentInstrumentFamilyView`
--

DROP TABLE IF EXISTS `PersonalizedInstrumentInstrumentFamilyView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedInstrumentInstrumentFamilyView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedInstrumentInstrumentFamilyView` AS SELECT
 1 AS `instrument_id`,
  1 AS `instrument_family_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedInstrumentInsurancesView`
--

DROP TABLE IF EXISTS `PersonalizedInstrumentInsurancesView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedInstrumentInsurancesView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedInstrumentInsurancesView` AS SELECT
 1 AS `object`,
  1 AS `accessory`,
  1 AS `manufacturer`,
  1 AS `year_of_construction`,
  1 AS `insurance_amount`,
  1 AS `start_of_insurance`,
  1 AS `id`,
  1 AS `deleted`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `instrument_holder_id`,
  1 AS `instrument_owner_id`,
  1 AS `bill_to_party_id`,
  1 AS `broker_id`,
  1 AS `geographical_scope`,
  1 AS `musician_id`,
  1 AS `is_debitor`,
  1 AS `is_holder`,
  1 AS `is_owner` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedInstrumentsView`
--

DROP TABLE IF EXISTS `PersonalizedInstrumentsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedInstrumentsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedInstrumentsView` AS SELECT
 1 AS `name`,
  1 AS `sort_order`,
  1 AS `deleted`,
  1 AS `id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedInsuranceBrokersView`
--

DROP TABLE IF EXISTS `PersonalizedInsuranceBrokersView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedInsuranceBrokersView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedInsuranceBrokersView` AS SELECT
 1 AS `short_name`,
  1 AS `long_name`,
  1 AS `address` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedInsuranceRatesView`
--

DROP TABLE IF EXISTS `PersonalizedInsuranceRatesView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedInsuranceRatesView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedInsuranceRatesView` AS SELECT
 1 AS `geographical_scope`,
  1 AS `rate`,
  1 AS `due_date`,
  1 AS `policy_number`,
  1 AS `broker_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedMusicianEmailAddressesView`
--

DROP TABLE IF EXISTS `PersonalizedMusicianEmailAddressesView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedMusicianEmailAddressesView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedMusicianEmailAddressesView` AS SELECT
 1 AS `address`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `musician_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedMusicianInstrumentsView`
--

DROP TABLE IF EXISTS `PersonalizedMusicianInstrumentsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedMusicianInstrumentsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedMusicianInstrumentsView` AS SELECT
 1 AS `ranking`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `deleted`,
  1 AS `musician_id`,
  1 AS `instrument_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedMusicianRowAccessTokensView`
--

DROP TABLE IF EXISTS `PersonalizedMusicianRowAccessTokensView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedMusicianRowAccessTokensView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedMusicianRowAccessTokensView` AS SELECT
 1 AS `user_id`,
  1 AS `access_token_hash`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `musician_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedMusiciansView`
--

DROP TABLE IF EXISTS `PersonalizedMusiciansView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedMusiciansView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedMusiciansView` AS SELECT
 1 AS `sur_name`,
  1 AS `first_name`,
  1 AS `nick_name`,
  1 AS `display_name`,
  1 AS `gender`,
  1 AS `user_id_slug`,
  1 AS `user_passphrase`,
  1 AS `city`,
  1 AS `street`,
  1 AS `street_number`,
  1 AS `address_supplement`,
  1 AS `po_box`,
  1 AS `country`,
  1 AS `postal_code`,
  1 AS `language`,
  1 AS `mobile_phone`,
  1 AS `fixed_line_phone`,
  1 AS `birthday`,
  1 AS `email`,
  1 AS `default_participation_status`,
  1 AS `remarks`,
  1 AS `cloud_account_deactivated`,
  1 AS `cloud_account_disabled`,
  1 AS `updated`,
  1 AS `address_book_uri`,
  1 AS `organization`,
  1 AS `job_title`,
  1 AS `id`,
  1 AS `created`,
  1 AS `deleted`,
  1 AS `uuid` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedProjectApplicationsView`
--

DROP TABLE IF EXISTS `PersonalizedProjectApplicationsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectApplicationsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedProjectApplicationsView` AS SELECT
 1 AS `email`,
  1 AS `password_hash`,
  1 AS `data`,
  1 AS `deleted`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `project_id`,
  1 AS `musician_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedProjectEventsView`
--

DROP TABLE IF EXISTS `PersonalizedProjectEventsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectEventsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedProjectEventsView` AS SELECT
 1 AS `calendar_id`,
  1 AS `calendar_uri`,
  1 AS `event_uid`,
  1 AS `series_uid`,
  1 AS `event_uri`,
  1 AS `recurrence_id`,
  1 AS `sequence`,
  1 AS `type`,
  1 AS `id`,
  1 AS `deleted`,
  1 AS `project_id`,
  1 AS `absence_field_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedProjectInstrumentationNumbersView`
--

DROP TABLE IF EXISTS `PersonalizedProjectInstrumentationNumbersView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectInstrumentationNumbersView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedProjectInstrumentationNumbersView` AS SELECT
 1 AS `voice`,
  1 AS `quantity`,
  1 AS `project_id`,
  1 AS `instrument_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedProjectInstrumentsView`
--

DROP TABLE IF EXISTS `PersonalizedProjectInstrumentsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectInstrumentsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedProjectInstrumentsView` AS SELECT
 1 AS `voice`,
  1 AS `section_leader`,
  1 AS `project_id`,
  1 AS `musician_id`,
  1 AS `instrument_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedProjectParticipantFieldsDataOptionsView`
--

DROP TABLE IF EXISTS `PersonalizedProjectParticipantFieldsDataOptionsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectParticipantFieldsDataOptionsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedProjectParticipantFieldsDataOptionsView` AS SELECT
 1 AS `key`,
  1 AS `label`,
  1 AS `data`,
  1 AS `balancing_account`,
  1 AS `deposit`,
  1 AS `limit`,
  1 AS `tooltip`,
  1 AS `deleted`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `field_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedProjectParticipantFieldsDataView`
--

DROP TABLE IF EXISTS `PersonalizedProjectParticipantFieldsDataView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectParticipantFieldsDataView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedProjectParticipantFieldsDataView` AS SELECT
 1 AS `option_key`,
  1 AS `option_value`,
  1 AS `deposit`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `deleted`,
  1 AS `field_id`,
  1 AS `project_id`,
  1 AS `musician_id`,
  1 AS `supporting_document_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedProjectParticipantFieldsView`
--

DROP TABLE IF EXISTS `PersonalizedProjectParticipantFieldsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectParticipantFieldsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedProjectParticipantFieldsView` AS SELECT
 1 AS `id`,
  1 AS `name`,
  1 AS `multiplicity`,
  1 AS `data_type`,
  1 AS `due_date`,
  1 AS `deposit_due_date`,
  1 AS `balancing_account`,
  1 AS `tooltip`,
  1 AS `tab`,
  1 AS `display_order`,
  1 AS `participation_context`,
  1 AS `encrypted`,
  1 AS `participant_access`,
  1 AS `deleted`,
  1 AS `project_id`,
  1 AS `default_value` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedProjectParticipantsView`
--

DROP TABLE IF EXISTS `PersonalizedProjectParticipantsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectParticipantsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedProjectParticipantsView` AS SELECT
 1 AS `registration`,
  1 AS `participation_status`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `deleted`,
  1 AS `project_id`,
  1 AS `musician_id`,
  1 AS `database_documents_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedProjectPaymentsView`
--

DROP TABLE IF EXISTS `PersonalizedProjectPaymentsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectPaymentsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedProjectPaymentsView` AS SELECT
 1 AS `amount`,
  1 AS `is_donation`,
  1 AS `subject`,
  1 AS `id`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `field_id`,
  1 AS `project_id`,
  1 AS `musician_id`,
  1 AS `receivable_key`,
  1 AS `composite_payment_id`,
  1 AS `balance_documents_folder_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedProjectWebPagesView`
--

DROP TABLE IF EXISTS `PersonalizedProjectWebPagesView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectWebPagesView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedProjectWebPagesView` AS SELECT
 1 AS `article_id`,
  1 AS `article_name`,
  1 AS `category_id`,
  1 AS `priority`,
  1 AS `project_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedProjectsView`
--

DROP TABLE IF EXISTS `PersonalizedProjectsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedProjectsView` AS SELECT
 1 AS `year`,
  1 AS `name`,
  1 AS `type`,
  1 AS `mailing_list_id`,
  1 AS `registration_start_date`,
  1 AS `registration_deadline`,
  1 AS `id`,
  1 AS `deleted`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `financial_balance_documents_storage_id`,
  1 AS `registration_calendar_event_id`,
  1 AS `club_members`,
  1 AS `executive_board` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedSepaBankAccountsView`
--

DROP TABLE IF EXISTS `PersonalizedSepaBankAccountsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedSepaBankAccountsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedSepaBankAccountsView` AS SELECT
 1 AS `sequence`,
  1 AS `iban`,
  1 AS `bic`,
  1 AS `blz`,
  1 AS `bank_account_owner`,
  1 AS `deleted`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `musician_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedSepaDebitMandatesView`
--

DROP TABLE IF EXISTS `PersonalizedSepaDebitMandatesView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedSepaDebitMandatesView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedSepaDebitMandatesView` AS SELECT
 1 AS `sequence`,
  1 AS `mandate_reference`,
  1 AS `non_recurring`,
  1 AS `mandate_date`,
  1 AS `pre_notification_calendar_days`,
  1 AS `pre_notification_business_days`,
  1 AS `last_used_date`,
  1 AS `deleted`,
  1 AS `created`,
  1 AS `updated`,
  1 AS `musician_id`,
  1 AS `bank_account_sequence`,
  1 AS `project_id`,
  1 AS `written_mandate_id` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `PersonalizedTableFieldTranslationsView`
--

DROP TABLE IF EXISTS `PersonalizedTableFieldTranslationsView`;
/*!50001 DROP VIEW IF EXISTS `PersonalizedTableFieldTranslationsView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `PersonalizedTableFieldTranslationsView` AS SELECT
 1 AS `id`,
  1 AS `locale`,
  1 AS `object_class`,
  1 AS `field`,
  1 AS `foreign_key`,
  1 AS `content` */;
SET character_set_client = @saved_cs_client;

--
-- Dumping routines for database 'cafevdb_cloud_connector'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `AUTHORIZED_MUSICIAN_ID` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`phpunit`@`%` FUNCTION `AUTHORIZED_MUSICIAN_ID`() RETURNS int(11)
    READS SQL DATA
    SQL SECURITY INVOKER
BEGIN
  DECLARE musician_id INT;
  SET musician_id = cafevdb_cloud_connector.CLOUD_USER_MUSICIAN_ID();
  IF musician_id > 0 THEN
    RETURN musician_id;
  END IF;
  RETURN cafevdb_cloud_connector.PROJECT_APPLICATION_MUSICIAN_ID();
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `BIN2UUID` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`phpunit`@`%` FUNCTION `BIN2UUID`(`b` BINARY(16)) RETURNS char(36) CHARSET ascii COLLATE ascii_general_ci
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
  RETURN BIN_TO_UUID(b, 0);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `BIN_TO_UUID` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`phpunit`@`%` FUNCTION `BIN_TO_UUID`(`b` BINARY(16), `f` BOOLEAN) RETURNS char(36) CHARSET ascii COLLATE ascii_general_ci
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
  DECLARE hexStr CHAR(32);
  SET hexStr = HEX(b);
  RETURN LOWER(CONCAT(
           IF(f,SUBSTR(hexStr, 9, 8),SUBSTR(hexStr, 1, 8)), '-',
           IF(f,SUBSTR(hexStr, 5, 4),SUBSTR(hexStr, 9, 4)), '-',
           IF(f,SUBSTR(hexStr, 1, 4),SUBSTR(hexStr, 13, 4)), '-',
           SUBSTR(hexStr, 17, 4), '-',
           SUBSTR(hexStr, 21)
        ));
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `CLOUD_USER_ID` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`phpunit`@`%` FUNCTION `CLOUD_USER_ID`() RETURNS varchar(256) CHARSET ascii COLLATE ascii_general_ci
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
  RETURN @CLOUD_USER_ID;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `CLOUD_USER_MUSICIAN_ID` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`phpunit`@`%` FUNCTION `CLOUD_USER_MUSICIAN_ID`() RETURNS int(11)
    READS SQL DATA
BEGIN
  DECLARE musician_id INT;
  SET musician_id = 0;
  SELECT t.musician_id INTO musician_id FROM
      `cafevdb`.MusicianRowAccessTokens t
  WHERE
    (t.user_id = cafevdb_cloud_connector.CLOUD_USER_ID()
      AND t.access_token_hash = cafevdb_cloud_connector.ROW_ACCESS_TOKEN());
  RETURN musician_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `PROJECT_APPLICATION_MUSICIAN_ID` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`phpunit`@`%` FUNCTION `PROJECT_APPLICATION_MUSICIAN_ID`() RETURNS int(11)
    READS SQL DATA
BEGIN
  DECLARE musician_id INT;
  SET musician_id = 0;
  SELECT t.musician_id INTO musician_id FROM
      `cafevdb`.ProjectApplications t
    WHERE
      (FIND_IN_SET(SHA2(t.email, 256), cafevdb_cloud_connector.PROJECT_APPLICATION_SHARE_TOKENS()) > 0
        AND t.password_hash = cafevdb_cloud_connector.PROJECT_APPLICATION_ROW_ACCESS_TOKEN());
  RETURN musician_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `PROJECT_APPLICATION_PROJECT_ID` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`phpunit`@`%` FUNCTION `PROJECT_APPLICATION_PROJECT_ID`() RETURNS int(11)
    READS SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
  DECLARE project_id INT;
  SET project_id = 0;
  SELECT p.id INTO project_id FROM
    `cafevdb`.Projects p
  WHERE
    p.name = cafevdb_cloud_connector.PROJECT_APPLICATION_PROJECT_NAME();
  RETURN project_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `PROJECT_APPLICATION_PROJECT_NAME` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`phpunit`@`%` FUNCTION `PROJECT_APPLICATION_PROJECT_NAME`() RETURNS varchar(1024) CHARSET ascii COLLATE ascii_general_ci
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
  RETURN @PROJECT_APPLICATION_PROJECT_NAME;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `PROJECT_APPLICATION_ROW_ACCESS_TOKEN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`phpunit`@`%` FUNCTION `PROJECT_APPLICATION_ROW_ACCESS_TOKEN`() RETURNS char(128) CHARSET ascii COLLATE ascii_general_ci
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
  RETURN @PROJECT_APPLICATION_ROW_ACCESS_TOKEN;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `PROJECT_APPLICATION_SHARE_TOKENS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`phpunit`@`%` FUNCTION `PROJECT_APPLICATION_SHARE_TOKENS`() RETURNS varchar(1024) CHARSET ascii COLLATE ascii_general_ci
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
  RETURN @PROJECT_APPLICATION_SHARE_TOKENS;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `ROW_ACCESS_TOKEN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`phpunit`@`%` FUNCTION `ROW_ACCESS_TOKEN`() RETURNS char(128) CHARSET ascii COLLATE ascii_general_ci
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
  RETURN @ROW_ACCESS_TOKEN;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `UUID2BIN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`phpunit`@`%` FUNCTION `UUID2BIN`(`uuid` CHAR(36)) RETURNS binary(16)
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
  RETURN UUID_TO_BIN(uuid, 0);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `UUID_TO_BIN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`phpunit`@`%` FUNCTION `UUID_TO_BIN`(`uuid` CHAR(36), `f` BOOLEAN) RETURNS binary(16)
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
  RETURN UNHEX(CONCAT(
  IF(f,SUBSTRING(uuid, 15, 4),SUBSTRING(uuid, 1, 8)),
  SUBSTRING(uuid, 10, 4),
  IF(f,SUBSTRING(uuid, 1, 8),SUBSTRING(uuid, 15, 4)),
  SUBSTRING(uuid, 20, 4),
  SUBSTRING(uuid, 25))
  );
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `NextcloudGroupView`
--

/*!50001 DROP VIEW IF EXISTS `NextcloudGroupView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `NextcloudGroupView` AS select convert(concat(_ascii'cafevdb_',`p`.`id`) collate ascii_bin using utf8mb4) AS `gid`,`p`.`name` AS `display_name`,0 AS `is_admin` from `cafevdb`.`Projects` `p` where `p`.`type` in ('temporary','permanent') and `p`.`deleted` is null */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `NextcloudUserGroupView`
--

/*!50001 DROP VIEW IF EXISTS `NextcloudUserGroupView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `NextcloudUserGroupView` AS select convert(`m`.`user_id_slug` using utf8mb4) AS `uid`,convert(concat(_ascii'cafevdb_',`p`.`id`) collate ascii_bin using utf8mb4) AS `gid` from ((`cafevdb`.`ProjectParticipants` `pp` left join `cafevdb`.`Musicians` `m` on(`m`.`id` = `pp`.`musician_id`)) left join `cafevdb`.`Projects` `p` on(`p`.`id` = `pp`.`project_id`)) where `pp`.`deleted` is null */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `NextcloudUserView`
--

/*!50001 DROP VIEW IF EXISTS `NextcloudUserView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `NextcloudUserView` AS select convert(`m`.`user_id_slug` using utf8mb4) AS `uid`,`m`.`user_passphrase` AS `password`,concat_ws(' ',if(`m`.`nick_name` is null or `m`.`nick_name` = '',`m`.`first_name`,`m`.`nick_name`),`m`.`sur_name`) AS `name`,convert(`m`.`email` using utf8mb4) AS `email`,NULL AS `quota`,NULL AS `home`,coalesce(`m`.`cloud_account_deactivated`,0) AS `inactive`,if(`m`.`deleted` is not null or `m`.`cloud_account_disabled` = 1,1,0) AS `disabled`,1 AS `avatar`,NULL AS `salt` from `cafevdb`.`Musicians` `m` where `m`.`email` is not null and `m`.`email` <> '' */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedCompositePaymentsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedCompositePaymentsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedCompositePaymentsView` AS select `t`.`amount` AS `amount`,`t`.`date_of_receipt` AS `date_of_receipt`,`t`.`subject` AS `subject`,`t`.`notification_message_id` AS `notification_message_id`,`t`.`id` AS `id`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`sepa_transaction_id` AS `sepa_transaction_id`,`t`.`musician_id` AS `musician_id`,`t`.`bank_account_sequence` AS `bank_account_sequence`,`t`.`debit_mandate_sequence` AS `debit_mandate_sequence`,`t`.`pre_notification_message_id` AS `pre_notification_message_id`,`t`.`project_id` AS `project_id`,`t`.`supporting_document_id` AS `supporting_document_id`,`t`.`balance_documents_folder_id` AS `balance_documents_folder_id` from `cafevdb`.`CompositePayments` `t` where `t`.`musician_id` = `cafevdb_cloud_connector`.`AUTHORIZED_MUSICIAN_ID`() */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedDatabaseStorageDirEntriesView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedDatabaseStorageDirEntriesView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedDatabaseStorageDirEntriesView` AS select `t`.`name` AS `name`,`t`.`id` AS `id`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`parent_id` AS `parent_id`,`t`.`type` AS `type`,`t`.`file_id` AS `file_id` from `cafevdb`.`DatabaseStorageDirEntries` `t` where `t`.`file_id` is null or `t`.`file_id` in (select `efov`.`encrypted_file_id` AS `file_id` from `cafevdb_cloud_connector`.`PersonalizedEncryptedFileOwnersView` `efov`) */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedEncryptedFileOwnersView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedEncryptedFileOwnersView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedEncryptedFileOwnersView` AS select `t`.`musician_id` AS `musician_id`,`t`.`encrypted_file_id` AS `encrypted_file_id` from `cafevdb`.`EncryptedFileOwners` `t` where `t`.`musician_id` = `cafevdb_cloud_connector`.`AUTHORIZED_MUSICIAN_ID`() */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedFilesView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedFilesView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedFilesView` AS select `t`.`file_name` AS `file_name`,`t`.`mime_type` AS `mime_type`,`t`.`size` AS `size`,`t`.`data_hash` AS `data_hash`,`t`.`updated` AS `updated`,`t`.`id` AS `id`,`t`.`created` AS `created`,`t`.`type` AS `type`,`t`.`width` AS `width`,`t`.`height` AS `height` from `cafevdb`.`Files` `t` where `t`.`id` in (select `efov`.`encrypted_file_id` AS `file_id` from `cafevdb_cloud_connector`.`PersonalizedEncryptedFileOwnersView` `efov`) */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedFileDataView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedFileDataView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedFileDataView` AS select `t`.`data_hash` AS `data_hash`,`t`.`data` AS `data`,`t`.`file_id` AS `file_id`,`t`.`type` AS `type` from (`cafevdb_cloud_connector`.`PersonalizedFilesView` `fv` join `cafevdb`.`FileData` `t` on(`t`.`file_id` = `fv`.`id`)) */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedGeoContinentsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedGeoContinentsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedGeoContinentsView` AS select `t`.`code` AS `code`,`t`.`target` AS `target`,`t`.`l10n_name` AS `l10n_name` from `cafevdb`.`GeoContinents` `t` */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedGeoCountriesView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedGeoCountriesView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedGeoCountriesView` AS select `t`.`iso` AS `iso`,`t`.`target` AS `target`,`t`.`l10n_name` AS `l10n_name`,`t`.`continent_code` AS `continent_code` from `cafevdb`.`GeoCountries` `t` */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedGeoPostalCodeTranslationsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedGeoPostalCodeTranslationsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedGeoPostalCodeTranslationsView` AS select `t`.`target` AS `target`,`t`.`translation` AS `translation`,`t`.`geo_postal_code_id` AS `geo_postal_code_id` from `cafevdb`.`GeoPostalCodeTranslations` `t` */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedGeoPostalCodesView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedGeoPostalCodesView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedGeoPostalCodesView` AS select `t`.`country` AS `country`,`t`.`state_province` AS `state_province`,`t`.`postal_code` AS `postal_code`,`t`.`name` AS `name`,`t`.`latitude` AS `latitude`,`t`.`longitude` AS `longitude`,`t`.`id` AS `id`,`t`.`created` AS `created`,`t`.`updated` AS `updated` from `cafevdb`.`GeoPostalCodes` `t` */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedInstrumentFamiliesView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedInstrumentFamiliesView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedInstrumentFamiliesView` AS select `t`.`family` AS `family`,`t`.`id` AS `id`,`t`.`deleted` AS `deleted` from `cafevdb`.`InstrumentFamilies` `t` */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedInstrumentInstrumentFamilyView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedInstrumentInstrumentFamilyView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedInstrumentInstrumentFamilyView` AS select `t`.`instrument_id` AS `instrument_id`,`t`.`instrument_family_id` AS `instrument_family_id` from `cafevdb`.`instrument_instrument_family` `t` */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedInstrumentInsurancesView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedInstrumentInsurancesView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedInstrumentInsurancesView` AS select `t`.`object` AS `object`,`t`.`accessory` AS `accessory`,`t`.`manufacturer` AS `manufacturer`,`t`.`year_of_construction` AS `year_of_construction`,`t`.`insurance_amount` AS `insurance_amount`,`t`.`start_of_insurance` AS `start_of_insurance`,`t`.`id` AS `id`,`t`.`deleted` AS `deleted`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`instrument_holder_id` AS `instrument_holder_id`,`t`.`instrument_owner_id` AS `instrument_owner_id`,`t`.`bill_to_party_id` AS `bill_to_party_id`,`t`.`broker_id` AS `broker_id`,`t`.`geographical_scope` AS `geographical_scope`,`at`.`musician_id` AS `musician_id`,`t`.`bill_to_party_id` is null or `at`.`musician_id` = `t`.`bill_to_party_id` AS `is_debitor`,`at`.`musician_id` = `t`.`instrument_holder_id` AS `is_holder`,`t`.`instrument_owner_id` is null or `at`.`musician_id` = `t`.`instrument_owner_id` AS `is_owner` from ((select `cafevdb_cloud_connector`.`AUTHORIZED_MUSICIAN_ID`() AS `musician_id`) `at` join `cafevdb`.`InstrumentInsurances` `t` on(`t`.`instrument_holder_id` = `at`.`musician_id` or `t`.`bill_to_party_id` = `at`.`musician_id` or `t`.`instrument_owner_id` = `at`.`musician_id`)) */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedInstrumentsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedInstrumentsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedInstrumentsView` AS select `t`.`name` AS `name`,`t`.`sort_order` AS `sort_order`,`t`.`deleted` AS `deleted`,`t`.`id` AS `id` from `cafevdb`.`Instruments` `t` */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedInsuranceBrokersView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedInsuranceBrokersView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedInsuranceBrokersView` AS select `t`.`short_name` AS `short_name`,`t`.`long_name` AS `long_name`,`t`.`address` AS `address` from `cafevdb`.`InsuranceBrokers` `t` */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedInsuranceRatesView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedInsuranceRatesView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedInsuranceRatesView` AS select `t`.`geographical_scope` AS `geographical_scope`,`t`.`rate` AS `rate`,`t`.`due_date` AS `due_date`,`t`.`policy_number` AS `policy_number`,`t`.`broker_id` AS `broker_id` from `cafevdb`.`InsuranceRates` `t` */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedMusicianEmailAddressesView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedMusicianEmailAddressesView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedMusicianEmailAddressesView` AS select `t`.`address` AS `address`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`musician_id` AS `musician_id` from `cafevdb`.`MusicianEmailAddresses` `t` where `t`.`musician_id` = `cafevdb_cloud_connector`.`AUTHORIZED_MUSICIAN_ID`() */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedMusicianInstrumentsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedMusicianInstrumentsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedMusicianInstrumentsView` AS select `t`.`ranking` AS `ranking`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`deleted` AS `deleted`,`t`.`musician_id` AS `musician_id`,`t`.`instrument_id` AS `instrument_id` from `cafevdb`.`MusicianInstruments` `t` where `t`.`musician_id` = `cafevdb_cloud_connector`.`AUTHORIZED_MUSICIAN_ID`() */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedMusicianRowAccessTokensView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedMusicianRowAccessTokensView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedMusicianRowAccessTokensView` AS select `t`.`user_id` AS `user_id`,`t`.`access_token_hash` AS `access_token_hash`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`musician_id` AS `musician_id` from `cafevdb`.`MusicianRowAccessTokens` `t` where `t`.`access_token_hash` = `cafevdb_cloud_connector`.`ROW_ACCESS_TOKEN`() and `t`.`user_id` = `cafevdb_cloud_connector`.`CLOUD_USER_ID`() */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedMusiciansView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedMusiciansView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedMusiciansView` AS select `m`.`sur_name` AS `sur_name`,`m`.`first_name` AS `first_name`,`m`.`nick_name` AS `nick_name`,`m`.`display_name` AS `display_name`,`m`.`gender` AS `gender`,`m`.`user_id_slug` AS `user_id_slug`,`m`.`user_passphrase` AS `user_passphrase`,`m`.`city` AS `city`,`m`.`street` AS `street`,`m`.`street_number` AS `street_number`,`m`.`address_supplement` AS `address_supplement`,`m`.`po_box` AS `po_box`,`m`.`country` AS `country`,`m`.`postal_code` AS `postal_code`,`m`.`language` AS `language`,`m`.`mobile_phone` AS `mobile_phone`,`m`.`fixed_line_phone` AS `fixed_line_phone`,`m`.`birthday` AS `birthday`,`m`.`email` AS `email`,`m`.`default_participation_status` AS `default_participation_status`,`m`.`remarks` AS `remarks`,`m`.`cloud_account_deactivated` AS `cloud_account_deactivated`,`m`.`cloud_account_disabled` AS `cloud_account_disabled`,`m`.`updated` AS `updated`,`m`.`address_book_uri` AS `address_book_uri`,`m`.`organization` AS `organization`,`m`.`job_title` AS `job_title`,`m`.`id` AS `id`,`m`.`created` AS `created`,`m`.`deleted` AS `deleted`,`m`.`uuid` AS `uuid` from `cafevdb`.`Musicians` `m` where `m`.`id` = `cafevdb_cloud_connector`.`AUTHORIZED_MUSICIAN_ID`() */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedProjectApplicationsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectApplicationsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedProjectApplicationsView` AS select `t`.`email` AS `email`,`t`.`password_hash` AS `password_hash`,`t`.`data` AS `data`,`t`.`deleted` AS `deleted`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`project_id` AS `project_id`,`t`.`musician_id` AS `musician_id` from `cafevdb`.`ProjectApplications` `t` where find_in_set(sha2(`t`.`email`,256),`cafevdb_cloud_connector`.`PROJECT_APPLICATION_SHARE_TOKENS`()) > 0 and `t`.`project_id` = `cafevdb_cloud_connector`.`PROJECT_APPLICATION_PROJECT_ID`() or `t`.`musician_id` = `cafevdb_cloud_connector`.`CLOUD_USER_MUSICIAN_ID`() */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedProjectEventsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectEventsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedProjectEventsView` AS select `t`.`calendar_id` AS `calendar_id`,`t`.`calendar_uri` AS `calendar_uri`,`t`.`event_uid` AS `event_uid`,`t`.`series_uid` AS `series_uid`,`t`.`event_uri` AS `event_uri`,`t`.`recurrence_id` AS `recurrence_id`,`t`.`sequence` AS `sequence`,`t`.`type` AS `type`,`t`.`id` AS `id`,`t`.`deleted` AS `deleted`,`t`.`project_id` AS `project_id`,`t`.`absence_field_id` AS `absence_field_id` from `cafevdb`.`ProjectEvents` `t` where `t`.`calendar_uri` in ('concerts','rehearsals','other') */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedProjectInstrumentationNumbersView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectInstrumentationNumbersView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedProjectInstrumentationNumbersView` AS select `t`.`voice` AS `voice`,`t`.`quantity` AS `quantity`,`t`.`project_id` AS `project_id`,`t`.`instrument_id` AS `instrument_id` from `cafevdb`.`ProjectInstrumentationNumbers` `t` */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedProjectInstrumentsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectInstrumentsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedProjectInstrumentsView` AS select `t`.`voice` AS `voice`,`t`.`section_leader` AS `section_leader`,`t`.`project_id` AS `project_id`,`t`.`musician_id` AS `musician_id`,`t`.`instrument_id` AS `instrument_id` from `cafevdb`.`ProjectInstruments` `t` where `t`.`musician_id` = `cafevdb_cloud_connector`.`AUTHORIZED_MUSICIAN_ID`() */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedProjectParticipantFieldsDataOptionsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectParticipantFieldsDataOptionsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedProjectParticipantFieldsDataOptionsView` AS select `t`.`key` AS `key`,`t`.`label` AS `label`,`t`.`data` AS `data`,`t`.`balancing_account` AS `balancing_account`,`t`.`deposit` AS `deposit`,`t`.`limit` AS `limit`,`t`.`tooltip` AS `tooltip`,`t`.`deleted` AS `deleted`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`field_id` AS `field_id` from (`cafevdb_cloud_connector`.`PersonalizedProjectParticipantFieldsView` `ppf` join `cafevdb`.`ProjectParticipantFieldsDataOptions` `t` on(`t`.`field_id` = `ppf`.`id`)) group by `t`.`field_id`,`t`.`key` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedProjectParticipantFieldsDataView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectParticipantFieldsDataView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedProjectParticipantFieldsDataView` AS select `t`.`option_key` AS `option_key`,`t`.`option_value` AS `option_value`,`t`.`deposit` AS `deposit`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`deleted` AS `deleted`,`t`.`field_id` AS `field_id`,`t`.`project_id` AS `project_id`,`t`.`musician_id` AS `musician_id`,`t`.`supporting_document_id` AS `supporting_document_id` from (`cafevdb`.`ProjectParticipantFieldsData` `t` join `cafevdb`.`ProjectParticipantFields` `ppf` on(`t`.`field_id` = `ppf`.`id` and `ppf`.`participant_access` <> 'none')) where `t`.`musician_id` = `cafevdb_cloud_connector`.`AUTHORIZED_MUSICIAN_ID`() */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedProjectParticipantFieldsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectParticipantFieldsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedProjectParticipantFieldsView` AS select `t`.`id` AS `id`,`t`.`name` AS `name`,`t`.`multiplicity` AS `multiplicity`,`t`.`data_type` AS `data_type`,`t`.`due_date` AS `due_date`,`t`.`deposit_due_date` AS `deposit_due_date`,`t`.`balancing_account` AS `balancing_account`,`t`.`tooltip` AS `tooltip`,`t`.`tab` AS `tab`,`t`.`display_order` AS `display_order`,`t`.`participation_context` AS `participation_context`,`t`.`encrypted` AS `encrypted`,`t`.`participant_access` AS `participant_access`,`t`.`deleted` AS `deleted`,`t`.`project_id` AS `project_id`,`t`.`default_value` AS `default_value` from (`cafevdb_cloud_connector`.`PersonalizedProjectsView` `p` join `cafevdb`.`ProjectParticipantFields` `t` on(`t`.`project_id` = `p`.`id`)) where `t`.`participant_access` <> 'none' group by `t`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedProjectParticipantsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectParticipantsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedProjectParticipantsView` AS select `t`.`registration` AS `registration`,`t`.`participation_status` AS `participation_status`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`deleted` AS `deleted`,`t`.`project_id` AS `project_id`,`t`.`musician_id` AS `musician_id`,`t`.`database_documents_id` AS `database_documents_id` from `cafevdb`.`ProjectParticipants` `t` where `t`.`musician_id` = `cafevdb_cloud_connector`.`AUTHORIZED_MUSICIAN_ID`() */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedProjectPaymentsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectPaymentsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedProjectPaymentsView` AS select `t`.`amount` AS `amount`,`t`.`is_donation` AS `is_donation`,`t`.`subject` AS `subject`,`t`.`id` AS `id`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`field_id` AS `field_id`,`t`.`project_id` AS `project_id`,`t`.`musician_id` AS `musician_id`,`t`.`receivable_key` AS `receivable_key`,`t`.`composite_payment_id` AS `composite_payment_id`,`t`.`balance_documents_folder_id` AS `balance_documents_folder_id` from `cafevdb`.`ProjectPayments` `t` where `t`.`musician_id` = `cafevdb_cloud_connector`.`AUTHORIZED_MUSICIAN_ID`() */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedProjectWebPagesView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectWebPagesView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedProjectWebPagesView` AS select `t`.`article_id` AS `article_id`,`t`.`article_name` AS `article_name`,`t`.`category_id` AS `category_id`,`t`.`priority` AS `priority`,`t`.`project_id` AS `project_id` from (`cafevdb_cloud_connector`.`PersonalizedProjectsView` `p` join `cafevdb`.`ProjectWebPages` `t` on(`t`.`project_id` = `p`.`id`)) group by `t`.`project_id`,`t`.`article_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedProjectsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedProjectsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedProjectsView` AS select `t`.`year` AS `year`,`t`.`name` AS `name`,`t`.`type` AS `type`,`t`.`mailing_list_id` AS `mailing_list_id`,`t`.`registration_start_date` AS `registration_start_date`,`t`.`registration_deadline` AS `registration_deadline`,`t`.`id` AS `id`,`t`.`deleted` AS `deleted`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`financial_balance_documents_storage_id` AS `financial_balance_documents_storage_id`,`t`.`registration_calendar_event_id` AS `registration_calendar_event_id`,`t`.`id` = -1 AS `club_members`,`t`.`id` = -1 AS `executive_board` from `cafevdb`.`Projects` `t` where `t`.`type` = 'temporary' or `t`.`type` = 'permanent' */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedSepaBankAccountsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedSepaBankAccountsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedSepaBankAccountsView` AS select `t`.`sequence` AS `sequence`,`t`.`iban` AS `iban`,`t`.`bic` AS `bic`,`t`.`blz` AS `blz`,`t`.`bank_account_owner` AS `bank_account_owner`,`t`.`deleted` AS `deleted`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`musician_id` AS `musician_id` from `cafevdb`.`SepaBankAccounts` `t` where `t`.`musician_id` = `cafevdb_cloud_connector`.`AUTHORIZED_MUSICIAN_ID`() */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedSepaDebitMandatesView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedSepaDebitMandatesView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedSepaDebitMandatesView` AS select `t`.`sequence` AS `sequence`,`t`.`mandate_reference` AS `mandate_reference`,`t`.`non_recurring` AS `non_recurring`,`t`.`mandate_date` AS `mandate_date`,`t`.`pre_notification_calendar_days` AS `pre_notification_calendar_days`,`t`.`pre_notification_business_days` AS `pre_notification_business_days`,`t`.`last_used_date` AS `last_used_date`,`t`.`deleted` AS `deleted`,`t`.`created` AS `created`,`t`.`updated` AS `updated`,`t`.`musician_id` AS `musician_id`,`t`.`bank_account_sequence` AS `bank_account_sequence`,`t`.`project_id` AS `project_id`,`t`.`written_mandate_id` AS `written_mandate_id` from `cafevdb`.`SepaDebitMandates` `t` where `t`.`musician_id` = `cafevdb_cloud_connector`.`AUTHORIZED_MUSICIAN_ID`() */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `PersonalizedTableFieldTranslationsView`
--

/*!50001 DROP VIEW IF EXISTS `PersonalizedTableFieldTranslationsView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`phpunit`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `PersonalizedTableFieldTranslationsView` AS select `t`.`id` AS `id`,`t`.`locale` AS `locale`,`t`.`object_class` AS `object_class`,`t`.`field` AS `field`,`t`.`foreign_key` AS `foreign_key`,`t`.`content` AS `content` from `cafevdb`.`TableFieldTranslations` `t` */
/*!50002 WITH CASCADED CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-03-04 12:23:09
