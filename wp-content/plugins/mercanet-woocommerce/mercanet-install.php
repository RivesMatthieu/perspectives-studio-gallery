<?php

/**
 * Mercanet_Install class
 */
class Mercanet_Install {
    const MERCANET_MERCHANT_ID_RECETTE = '211000021310001';
    const MERCANET_SECRET_KEY_RECETTE = 'S9i8qClCnb2CZU3y3Vn0toIOgz3z_aBi79akR30vM9o';
    const MERCANET_KEY_VERSION_RECETTE = '1';
    
    public static function deactivation() {
        delete_option( 'mercanet_activation_key' );
    }

    public static function install() {
        // Test mode
        $check = get_option('mercanet_test_mode');
        if(empty($check) || $check == "yes") {
            update_option( 'mercanet_test_mode', 'yes' );            
            update_option( 'mercanet_merchant_id', self::MERCANET_MERCHANT_ID_RECETTE);            
            update_option( 'mercanet_secret_key', self::MERCANET_SECRET_KEY_RECETTE);            
            update_option( 'mercanet_version_key', self::MERCANET_KEY_VERSION_RECETTE);            
        }
 
        $admin_credential = new Mercanet_Admin_Credentials();
        $admin_credential->init_general_settings();
        
        
        // URL Payment
        update_option( 'MERCANET_PAYMENT_PAGE_URL_TEST', 'https://payment-webinit-mercanet.test.sips-atos.com/paymentInit' );
        update_option( 'MERCANET_PAYMENT_PAGE_URL', 'https://payment-webinit.mercanet.bnpparibas.net/paymentInit' );
        update_option( 'MERCANET_PAYMENT_PAGE_INTERFACE_VERSION',  'HP_2.10' );

        // URL Wallet
        update_option( 'MERCANET_WALLET_URL_TEST', 'https://payment-webinit-mercanet.test.sips-atos.com/walletManagementInit' );
        update_option( 'MERCANET_WALLET_URL', 'https://payment-webinit.mercanet.bnpparibas.net/walletManagementInit' );
        update_option( 'MERCANET_WALLET_INTERFACE_VERSION', 'HP_2.0' );

        // URL WebService
        update_option( 'MERCANET_WS_URL_TEST', 'https://office-server-mercanet.test.sips-atos.com/rs-services/v2/' );
        update_option( 'MERCANET_WS_URL', 'https://office-server-mercanet.bnpparibas.net/rs-services/v2/' );
        update_option( 'MERCANET_WS_INTERFACE_VERSION', 'CR_WS_2.6' );

        // Log
        update_option( 'mercanet_log_active', 'yes' );

        global $wpdb;

        $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mercanet_transaction` (
            `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
            `transaction_date` datetime NULL,
            `order_id` int(11) NULL,
            `authorization_id` varchar(255) NULL,
            `transaction_reference` varchar(255) NULL,
            `masked_pan` varchar(255) NULL,
            `amount` decimal(20,2) NULL,
            `transaction_type` varchar(255) NULL,
            `payment_mean_brand` varchar(255) NULL,
            `payment_mean_type` varchar(255) NULL,
            `response_code` varchar(255) NULL,
            `acquirer_response_code` varchar(255) NULL,
            `complementary_code` varchar(255) NULL,
            `complementary_info` varchar(255) NULL,
            `raw_data` text NULL,
            PRIMARY KEY (`transaction_id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8"
        );

        $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mercanet_response_code` (
            `response_code` varchar(10) NOT NULL,
            `rc_locale` varchar(10) NOT NULL,
            `rc_message` varchar(255) NOT NULL,
            KEY `response_code` (`response_code`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8"
        );

        $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mercanet_complementary_code` (
            `complementary_code` varchar(10) NOT NULL,
            `cc_locale` varchar(10) NOT NULL,
            `cc_message` varchar(255) NOT NULL,
            KEY `complementary_code` (`complementary_code`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8"
        );

        $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mercanet_acquirer_response_code` (
            `acquirer_response_code` varchar(10) NOT NULL,
            `arc_locale` varchar(10) NOT NULL,
            `arc_message` varchar(255) NOT NULL,
            KEY `acquirer_response_code` (`acquirer_response_code`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8"
        );

        $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mercanet_wallet` (
            `wallet_id` varchar(50) NOT NULL UNIQUE,
            `user_id` int(11) NOT NULL UNIQUE
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8"
        );
        
        $wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mercanet_schedule` (
            `mercanet_schedule_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `mercanet_transaction_id` int(11) NULL,
            `transaction_reference` varchar(255) NOT NULL,
            `masked_pan` varchar(255) NULL,
            `amount` decimal(20,2) NOT NULL,
            `date_add` datetime NOT NULL,
            `date_to_capture` datetime NOT NULL,
            `date_capture` datetime NULL,
            `captured` int(11) NOT NULL,
            `status` varchar(255) NOT NULL,
            PRIMARY KEY (`mercanet_schedule_id`),
            KEY `order_id` (`order_id`,`mercanet_transaction_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8"
        );
                
        $wpdb->query("CREATE TABLE `{$wpdb->prefix}mercanet_payment_recurring` (
            `id_mercanet_payment_recurring` int(10) NOT NULL AUTO_INCREMENT,
            `id_product` int(10) NOT NULL,
            `type` int(10) DEFAULT NULL,
            `periodicity` varchar(10) NOT NULL,
            `number_occurences` int(10) NOT NULL,
            `recurring_amount` float DEFAULT NULL,
            PRIMARY KEY (`id_mercanet_payment_recurring`),
            KEY `id_product` (`id_product`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8"
        );
        
        $wpdb->query("CREATE TABLE `{$wpdb->prefix}mercanet_customer_payment_recurring` (
            `id_mercanet_customer_payment_recurring` int(10) NOT NULL AUTO_INCREMENT,
            `id_product` int(10) NOT NULL,
            `id_tax_rules_group` int(10) NOT NULL,
            `id_order` int(10) NOT NULL,
            `id_customer` int(10) NOT NULL,
            `id_mercanet_transaction` int(10) NOT NULL,
            `status` int(10) NOT NULL,
            `amount_tax_exclude` float NOT NULL,
            `periodicity` varchar(10) NOT NULL,
            `number_occurences` int(10) NOT NULL,
            `current_occurence` int(10) NOT NULL DEFAULT '0',
            `date_add` datetime DEFAULT NULL,
            `last_schedule` datetime DEFAULT NULL,
            `next_schedule` datetime DEFAULT NULL,
            `current_specific_price` decimal(5,2) NOT NULL DEFAULT '0',
            `id_cart_paused_currency` int(10) NOT NULL DEFAULT '1',
            PRIMARY KEY (`id_mercanet_customer_payment_recurring`),
            KEY `id_product` (`id_product`,`id_tax_rules_group`,`id_order`,`id_customer`,`id_mercanet_transaction`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8"
        );


        $locale_fr = 'fr_FR';
        $locale_en = 'en_US';

        $wpdb->query("TRUNCATE TABLE  `{$wpdb->prefix}mercanet_response_code`");
        $wpdb->query("INSERT INTO  `{$wpdb->prefix}mercanet_response_code` (`response_code`, `rc_locale`, `rc_message`) VALUES
            ('00', '{$locale_en}', 'Authorisation accepted'),
            ('00', '{$locale_fr}', 'Autorisation acceptée'),

            ('02', '{$locale_en}', 'Authorisation request to be performed via telephone with the issuer, as the card authorisation threshold has been exceeded. You need to be authorised to force transactions'),
            ('02', '{$locale_fr}', 'Demande d’autorisation par téléphone à la banque à cause d’un dépassement du plafond d’autorisation sur la carte, si vous êtes autorisé à forcer les transactions.'),

            ('03', '{$locale_en}', 'Invalid Merchant contract'),
            ('03', '{$locale_fr}', 'Contrat commerçant invalide'),

            ('05', '{$locale_en}', 'Authorisation refused'),
            ('05', '{$locale_fr}', 'Autorisation refusée'),

            ('11', '{$locale_en}', 'Used for differed check. The PAN is blocked.'),
            ('11', '{$locale_fr}', 'Utilisé pour plusieurs contrôle, la carte est bloquée'),

            ('12', '{$locale_en}', 'Invalid transaction, check the request parameters'),
            ('12', '{$locale_fr}', 'Transaction invalide, vérifier les paramètres transférés dans la requête'),

            ('14', '{$locale_en}', 'Invalid PAN or payment mean data (ex: card security code)'),
            ('14', '{$locale_fr}', 'Coordonnées du moyen de paiement invalides (ex: n° de carte ou cryptogramme visuel de la carte)'),

            ('17', '{$locale_en}', 'Buyer cancellation'),
            ('17', '{$locale_fr}', 'Annulation de l’internaute'),

            ('24', '{$locale_en}', 'Operation not authorized. The operation you wish to perform is not compliant with the transaction status'),
            ('24', '{$locale_fr}', 'Opération impossible. L\'opération que vous souhaitez réaliser n’est pas compatible avec l\'état de la transaction.'),

            ('25', '{$locale_en}', 'Transaction unknown by Sips'),
            ('25', '{$locale_fr}', 'Transaction non trouvée dans la base de données Mercanet'),

            ('30', '{$locale_en}', 'Format error'),
            ('30', '{$locale_fr}', 'Erreur de format'),

            ('34', '{$locale_en}', 'Fraud suspicion'),
            ('34', '{$locale_fr}', 'Suspicion de fraude'),

            ('40', '{$locale_en}', 'Function not supported: the operation that you wish to perform is not part of the operation type for which you are authorised '),
            ('40', '{$locale_fr}', 'Fonction non supportée : l\'opération que vous souhaitez réaliser ne fait pas partie de la liste des opérations auxquelles vous êtes autorisés'),

            ('51', '{$locale_en}', 'Amount too high'),
            ('51', '{$locale_fr}', 'Montant trop élevé'),

            ('54', '{$locale_en}', 'Payment mean expiry date is past'),
            ('54', '{$locale_fr}', 'Date de validité du moyen de paiement est dépassée'),

            ('60', '{$locale_en}', 'Transaction pending'),
            ('60', '{$locale_fr}', 'Transaction en attente'),

            ('63', '{$locale_en}', 'Security rules not observed, transaction stopped'),
            ('63', '{$locale_fr}', 'Règles de sécurité non respectées, transaction arrêtée'),

            ('75', '{$locale_en}', 'Exceeded number of PAN attempts'),
            ('75', '{$locale_fr}', 'Nombre de tentatives de saisie des coordonnées du moyen de paiement dépassé'),

            ('90', '{$locale_en}', 'Service temporarily not available'),
            ('90', '{$locale_fr}', 'Service temporairement indisponible'),

            ('94', '{$locale_en}', 'Duplicated transaction: the transactionReference has been used previously'),
            ('94', '{$locale_fr}', 'Transaction dupliquée : le transactionReference de la transaction a déjà été utilisé'),

            ('97', '{$locale_en}', 'Time frame exceeded, transaction refused'),
            ('97', '{$locale_fr}', 'Délais expiré, transation refusée'),

            ('99', '{$locale_en}', 'Temporary problem at the Sips server level'),
            ('99', '{$locale_fr}', 'Problème temporaire au niveau du serveur Mercanet')"
        );

        $wpdb->query("TRUNCATE TABLE  `{$wpdb->prefix}mercanet_complementary_code`");
        $wpdb->query("INSERT INTO  `{$wpdb->prefix}mercanet_complementary_code` (`complementary_code`, `cc_locale`, `cc_message`) VALUES
            ('00', '{$locale_en}', 'All  controls  that  you  adhered  to  have  been  successfully completed'),
            ('00', '{$locale_fr}', 'Tous les contrôles auxquels vous avez adhérés se sont effectués avec succès'),

            ('02', '{$locale_en}', 'The card used has exceeded the authorised balance limit'),
            ('02', '{$locale_fr}', 'La carte utilisée a dépassé l’encours autorisé'),

            ('03', '{$locale_en}', 'The card used is on the merchant\'s « grey list »'),
            ('03', '{$locale_fr}', 'La carte utilisée appartient à la « liste grise » du commerçant'),

            ('05', '{$locale_en}', 'The  BIN  of  the  card  used  belongs  to  a  range  which  is  not referenced on Sip\'s platform BIN table'),
            ('05', '{$locale_fr}', 'Le BIN de la carte utilisée appartient à une plage non référencée dans la table des BIN de la plate-forme Mercanet'),

            ('06', '{$locale_en}', 'The country code related to the card number is not on the list of countries allowed by the merchant'),
            ('06', '{$locale_fr}', 'Le numéro de carte n\'est pas dans une plage de même nationalité que celle du commerçant'),

            ('07', '{$locale_en}', 'Virtual card (e-card) detected'),
            ('07', '{$locale_fr}', 'e-Carte Bleue détectée'),

            ('08', '{$locale_en}', 'The card BIN is present in a range on the merchant\'s « grey list »'),
            ('08', '{$locale_fr}', 'Plage de BIN KO'),

            ('09', '{$locale_en}', 'Unknown country IP'),
            ('09', '{$locale_fr}', 'Pays IP inconnu'),

            ('10', '{$locale_en}', 'Denied country IP '),
            ('10', '{$locale_fr}', 'Pays IP interdit'),

            ('11', '{$locale_en}', 'Card in hot/black list'),
            ('11', '{$locale_fr}', 'Carte dans OPPOTOTA'),

            ('12', '{$locale_en}', 'Country card / IP address country combination'),
            ('12', '{$locale_fr}', 'Combinaison pays carte/IP interdite'),

            ('13', '{$locale_en}', 'Unknown country IP or card. The country code cannot be determined from the card number'),
            ('13', '{$locale_fr}', 'Pays IP ou carte inconnu. Le code pays n\'est pas déterminable à partir du numéro de carte'),

            ('14', '{$locale_en}', 'Systematic authorisation card'),
            ('14', '{$locale_fr}', 'Carte à autorisation systématique'),

            ('15', '{$locale_en}', 'Unknown BIN (on control of systematic authorisation card) '),
            ('15', '{$locale_fr}', 'BIN inconnu (sur le contrôle de carte à autorisation systématique)'),

            ('16', '{$locale_en}', 'IP address in progress exceeded'),
            ('16', '{$locale_fr}', 'En-cours IP KO'),

            ('17', '{$locale_en}', 'Blocking related the status of the 3-D Secure authentication process '),
            ('17', '{$locale_fr}', 'Blocage dû au résultat du processus d’authentification 3D Secure'),

            ('18', '{$locale_en}', 'The card number is a commercial card number'),
            ('18', '{$locale_fr}', 'Le numéro de carte correspond à un numéro de carte commerciale'),

            ('19', '{$locale_en}', 'The card number is not part of the CB scheme'),
            ('19', '{$locale_fr}', 'Le numéro de carte n\'appartient pas au réseau CB'),

            ('20', '{$locale_en}', 'Customer ID in progress exceeded '),
            ('20', '{$locale_fr}', 'En-cours client dépassé'),

            ('21', '{$locale_en}', 'Maximum number of customer ID per card exceeded'),
            ('21', '{$locale_fr}', 'En-cours client par carte dépassé'),

            ('22', '{$locale_en}', 'Maximum number of cards per customer ID exceeded'),
            ('22', '{$locale_fr}', 'En-cours de carte par client dépassé'),

            ('3L', '{$locale_en}', 'Reason of the refusal of the transaction which is the transaction is not guaranteed by any entity (acquirer, wallet provider, etc.)'),
            ('3L', '{$locale_fr}', 'Refus de la transaction en raison de non garantie de la transaction par une entité (l\'acquéreur, le fournisseur de portefeuille, etc)'),

            ('99', '{$locale_en}', 'The Sips server encountered a problem during the processing of one of the additional local checks'),
            ('99', '{$locale_fr}', 'Le serveur Mercanet a un rencontré un problème lors du traitement d’un des contrôles locaux complémentaires')"
        );

        $wpdb->query("TRUNCATE TABLE  `{$wpdb->prefix}mercanet_acquirer_response_code`");
        $wpdb->query("INSERT INTO  `{$wpdb->prefix}mercanet_acquirer_response_code` (`acquirer_response_code`, `arc_locale`, `arc_message`) VALUES
            ('00', '{$locale_en}', 'Transaction approved or processed successfully'),
            ('00', '{$locale_fr}', 'Transaction approuvée ou traitée avec succès'),

            ('02', '{$locale_en}', 'Contact payment mean issuer'),
            ('02', '{$locale_fr}', 'Contactez l\'émetteur du moyen de paiement'),

            ('03', '{$locale_en}', 'Invalid acceptor'),
            ('03', '{$locale_fr}', 'Accepteur invalide'),

            ('04', '{$locale_en}', 'Keep the payment mean'),
            ('04', '{$locale_fr}', 'Conservez le support du moyen de paiement'),

            ('05', '{$locale_en}', 'Do not honour'),
            ('05', '{$locale_fr}', 'Ne pas honorer'),

            ('07', '{$locale_en}', 'Keep the payment mean, special conditions'),
            ('07', '{$locale_fr}', 'Conservez le support du moyen de paiement, conditions spéciales'),

            ('08', '{$locale_en}', 'Approve after identification'),
            ('08', '{$locale_fr}', 'Approuvez après l\'identification'),

            ('12', '{$locale_en}', 'Invalid transaction'),
            ('12', '{$locale_fr}', 'Transaction invalide'),

            ('13', '{$locale_en}', 'Invalid amount'),
            ('13', '{$locale_fr}', 'Montant invalide'),

            ('14', '{$locale_en}', 'Invalid PAN'),
            ('14', '{$locale_fr}', 'Coordonnées du moyen de paiement invalides'),

            ('15', '{$locale_en}', 'Unknown payment mean issuer'),
            ('15', '{$locale_fr}', 'Émetteur du moyen de paiement inconnu'),

            ('17', '{$locale_en}', 'Payment aborted by the buyer'),
            ('17', '{$locale_fr}', 'Paiement interrompu par l\'acheteur'),

            ('24', '{$locale_en}', 'Operation not authorised'),
            ('24', '{$locale_fr}', 'Opération impossible'),

            ('25', '{$locale_en}', 'Transaction not found'),
            ('25', '{$locale_fr}', 'Transaction inconnue'),

            ('30', '{$locale_en}', 'Format error'),
            ('30', '{$locale_fr}', 'Erreur de format'),

            ('31', '{$locale_en}', 'Id of the acquiring organisation unknown'),
            ('31', '{$locale_fr}', 'Id de l\'organisation d\'acquisition inconnu'),

            ('33', '{$locale_en}', 'Payment mean expired'),
            ('33', '{$locale_fr}', 'Moyen de paiement expiré'),

            ('34', '{$locale_en}', 'Fraud suspicion'),
            ('34', '{$locale_fr}', 'Suspicion de fraude'),

            ('40', '{$locale_en}', 'Function not supported'),
            ('40', '{$locale_fr}', 'Fonction non supportée'),

            ('41', '{$locale_en}', 'Payment mean lost'),
            ('41', '{$locale_fr}', 'Moyen de paiement perdu'),

            ('43', '{$locale_en}', 'Payment mean stolen'),
            ('43', '{$locale_fr}', 'Moyen de paiement volé'),

            ('51', '{$locale_en}', 'Insufficient or exceeded credit'),
            ('51', '{$locale_fr}', 'Provision insuffisante ou crédit dépassé'),

            ('54', '{$locale_en}', 'Payment mean expired'),
            ('54', '{$locale_fr}', 'Moyen de paiement expiré'),

            ('56', '{$locale_en}', 'Payment mean missing from the file'),
            ('56', '{$locale_fr}', 'Moyen de paiement manquant dans le fichier'),

            ('57', '{$locale_en}', 'Transaction unauthorised for this payment mean holder'),
            ('57', '{$locale_fr}', 'Transaction non autorisée pour ce porteur'),

            ('58', '{$locale_en}', 'Transaction forbidden to the terminal'),
            ('58', '{$locale_fr}', 'Transaction interdite au terminal'),

            ('59', '{$locale_en}', 'Fraud suspicion'),
            ('59', '{$locale_fr}', 'Suspicion de fraude'),

            ('60', '{$locale_en}', 'The payment mean acceptor must contact the acquirer'),
            ('60', '{$locale_fr}', 'L\'accepteur du moyen de paiement doit contacter l\'acquéreur'),

            ('61', '{$locale_en}', 'Exceeds the amount limit'),
            ('61', '{$locale_fr}', 'Excède le maximum autorisé'),

            ('62', '{$locale_en}', 'Transaction awaiting payment confirmation'),
            ('62', '{$locale_fr}', 'Transaction en attente de confirmation de paiement'),

            ('63', '{$locale_en}', 'Security rules not complied with'),
            ('63', '{$locale_fr}', 'Règles de sécurité non respectées'),

            ('65', '{$locale_en}', 'Allowed number of daily transactions has been exceeded'),
            ('65', '{$locale_fr}', 'Nombre de transactions du jour dépassé'),

            ('68', '{$locale_en}', 'Response not received or received too late'),
            ('68', '{$locale_fr}', 'Réponse non parvenue ou reçue trop tard'),

            ('75', '{$locale_en}', 'Exceeded number of PAN attempts'),
            ('75', '{$locale_fr}', 'Nombre de tentatives de saisie des coordonnées du moyen de paiement dépassé'),

            ('87', '{$locale_en}', 'Terminal unknown'),
            ('87', '{$locale_fr}', 'Terminal inconnu'),

            ('90', '{$locale_en}', 'System temporarily stopped'),
            ('90', '{$locale_fr}', 'Arrêt momentané du système'),

            ('91', '{$locale_en}', 'Payment mean issuer inaccessible'),
            ('91', '{$locale_fr}', 'Emetteur du moyen de paiement inaccessible'),

            ('92', '{$locale_en}', 'The transaction does not contain enough information to be routed to the authorizing agency'),
            ('92', '{$locale_fr}', 'La transaction ne contient pas les informations suffisantes pour être redirigées vers l\'organisme d\'autorisation'),

            ('94', '{$locale_en}', 'Duplicated transaction'),
            ('94', '{$locale_fr}', 'Transaction dupliquée'),

            ('96', '{$locale_en}', 'System malfunction'),
            ('96', '{$locale_fr}', 'Mauvais fonctionnement du système'),

            ('97', '{$locale_en}', 'Request time-out; transaction refused'),
            ('97', '{$locale_fr}', 'Requête expirée: transaction refusée'),

            ('98', '{$locale_en}', 'Server unavailable; network routing requested again'),
            ('98', '{$locale_fr}', 'Serveur inaccessible'),

            ('99', '{$locale_en}', 'Incident with initiator domain'),
            ('99', '{$locale_fr}', 'Incident technique')"
        );
    }
}
new Mercanet_Install();