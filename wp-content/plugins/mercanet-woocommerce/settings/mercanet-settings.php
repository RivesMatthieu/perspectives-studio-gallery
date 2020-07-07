<?php

/**
 * Check if the number is between the min and max value
 *
 * @param int
 * @return bool
 */
function is_between($number, $min, $max) {
    if (empty($number) || !isset($min) || empty($max)) {
        return false;
    }
    if ((int) $number >= (int) $min && (int) $number <= (int) $max) {
        return true;
    }
    return false;
}

/**
 * Validate the text length
 *
 * @param string, int
 * @return bool
 */
function validation_length($text, $number) {
    if (empty($text) || empty($number)) {
        return false;
    }
    if (strlen($text) <= $number) {
        return true;
    }
    return false;
}

/**
 * Get restricted cards list
 *
 * @return array
 */
function get_restricted_cards() {
    return array(
        'PLB' => 'PAYLIB',
        'MPS' => 'MASTERPASS',
        'AMX' => 'AMEX',
        'JCB' => 'JCB',
        'AUR' => 'CARTE AURORE',
        'PRE' => 'PRESTO',
        //'NCB' => 'NxCB',
        'FCB' => 'CETELEM',
        'PAL' => 'PAYPAL'
    );
}

/**
 * Get available cards list
 *
 * @return array
 */
function get_available_cards() {
    $return = array(
        'CB' => array(
            'id' => 'CB',
            'name' => __('Bank Card', 'mercanet'),
            'type' => 'CARD',
        ),
        'VISA' => array(
            'id' => 'VISA',
            'name' => __('VISA', 'mercanet'),
            'type' => 'CARD',
        ),
        'MASTERCARD' => array(
            'id' => 'MASTERCARD',
            'name' => __('MasterCard', 'mercanet'),
            'type' => 'CARD',
        ),
        'PAYPAL' => array(
            'id' => 'PAYPAL',
            'name' => __('Paypal', 'mercanet'),
            'type' => 'WALLET',
        ),
        'PAYLIB' => array(
            'id' => 'PAYLIB',
            'name' => __('PayLib', 'mercanet'),
            'type' => 'WALLET',
        ),
        'MASTERPASS' => array(
            'id' => 'MASTERPASS',
            'name' => __('MasterPass', 'mercanet'),
            'type' => 'WALLET',
        ),
        'AMEX' => array(
            'id' => 'AMEX',
            'name' => __('American Express', 'mercanet'),
            'type' => 'CARD',
        ),
        'JCB' => array(
            'id' => 'JCB',
            'name' => __('JCB', 'mercanet'),
            'type' => 'CARD',
        ),
        'CARTE AURORE' => array(
            'id' => 'CARTE AURORE',
            'name' => __('Card Aurore', 'mercanet'),
            'type' => 'CARD',
        ),
        'PRESTO' => array(
            'id' => 'PRESTO',
            'name' => __('Presto', 'mercanet'),
            'type' => 'CARD',
        ),
        'CETELEM_3X' => array(
            'id' => 'CETELEM_3X',
            'name' => __('Cetelem 3xCB', 'mercanet'),
            'type' => 'CARD',
        ),
        'CETELEM_4X' => array(
            'id' => 'CETELEM_4X',
            'name' => __('CETELEM 4xCB', 'mercanet'),
            'type' => 'CARD',
        ),
        'BCMC' => array(
            'id' => 'BCMC',
            'name' => __('Bancontact', 'mercanet'),
            'type' => 'CARD',
        ),
        'iDeal' => array(
            'id' => 'iDeal',
            'name' => __('iDeal', 'mercanet'),
            'type' => 'CREDIT_TRANSFER',
        ),
        'ELV' => array(
            'id' => 'ELV',
            'name' => __('Electronic Checks', 'mercanet'),
            'type' => 'DIRECT_DEBIT',
        ),
    );

    if (Mercanet_Api::is_allowed(array('ECV'))) {
        $return['ECV'] = array(
            'id' => 'ECV',
            'name' => __('Holiday vouchers', 'mercanet'),
            'type' => 'CARD',
        );
    }


    return $return;
}

/**
 * Get available languages list
 *
 * @return array
 */
function get_languages_available() {
    return array(
        'DE' => array(
            'id' => 'DE',
            'name' => 'Allemand'
        ),
        'EN' => array(
            'id' => 'EN',
            'name' => 'Anglais'
        ),
        'ES' => array(
            'id' => 'ES',
            'name' => 'Espagnol'
        ),
        'FR' => array(
            'id' => 'FR',
            'name' => 'Français'
        ),
        'HI' => array(
            'id' => 'HI',
            'name' => 'Hindi'
        ),
        'IT' => array(
            'id' => 'IT',
            'name' => 'Italien'
        ),
        'JA' => array(
            'id' => 'JA',
            'name' => 'Japonais'
        ),
        'NL' => array(
            'id' => 'NL',
            'name' => 'Néerlandais'
        ),
        'PT' => array(
            'id' => 'PT',
            'name' => 'Portugais'
        ),
        'RU' => array(
            'id' => 'RU',
            'name' => 'Russe'
        ),
        'SK' => array(
            'id' => 'SK',
            'name' => 'Slovaque'
        ),
        'ZH' => array(
            'id' => 'ZH',
            'name' => 'Chinois'
        )
    );
}

/**
 * Get available currencies list
 *
 * @return array
 */
function get_available_currencies() {
    return array(
        'CHF' => array(
            'id' => 'CHF',
            'iso' => '756',
            'name' => 'Franc Suisse',
        ),
        'GBP' => array(
            'id' => 'GBP',
            'iso' => '826',
            'name' => 'Livre Sterling',
        ),
        'USD' => array(
            'id' => 'USD',
            'iso' => '840',
            'name' => 'Dollar Américain',
        ),
        'ARS' => array(
            'id' => 'ARS',
            'iso' => '032',
            'name' => 'Peso Argentin'
        ),
        'AUD' => array(
            'id' => 'AUD',
            'iso' => '036',
            'name' => 'Dollar Australien',
        ),
        'KHR' => array(
            'id' => 'KHR',
            'iso' => '116',
            'name' => 'Riel',
        ),
        'CAD' => array(
            'id' => 'CAD',
            'iso' => '124',
            'name' => 'Dollar Canadien',
        ),
        'DKK' => array(
            'id' => 'DKK',
            'iso' => '208',
            'name' => 'Couronne Danoise',
        ),
        'INR' => array(
            'id' => 'INR',
            'iso' => '356',
            'name' => 'Roupie indienne',
        ),
        'JPY' => array(
            'id' => 'JPY',
            'iso' => '392',
            'name' => 'Yen',
        ),
        'KRW' => array(
            'id' => 'KRW',
            'iso' => '410',
            'name' => 'Won',
        ),
        'MXN' => array(
            'id' => 'MXN',
            'iso' => '484',
            'name' => 'Peso Mexicain',
        ),
        'NZD' => array(
            'id' => 'NZD',
            'iso' => '554',
            'name' => 'Dollar Néo-Zélandais',
        ),
        'NOK' => array(
            'id' => 'NOK',
            'iso' => '578',
            'name' => 'Couronne Norvégienne',
        ),
        'SGD' => array(
            'id' => 'SGD',
            'iso' => '702',
            'name' => 'Dollar de Singapour',
        ),
        'SEK' => array(
            'id' => 'SEK',
            'iso' => '752',
            'name' => 'Couronne Suédoise',
        ),
        'TWD' => array(
            'id' => 'TWD',
            'iso' => '901',
            'name' => 'Dollar de Taiwan',
        ),
        'TRY' => array(
            'id' => 'TRY',
            'iso' => '949',
            'name' => 'Nouvelle Livre Turque',
        ),
        'XOF' => array(
            'id' => 'XOF',
            'iso' => '952',
            'name' => 'Franc CFA',
        ),
        'XPF' => array(
            'id' => 'XPF',
            'iso' => '953',
            'name' => 'Franc Pacifique',
        ),
        'EUR' => array(
            'id' => 'EUR',
            'iso' => '978',
            'name' => 'Euro',
        ),
        'BRL' => array(
            'id' => 'BRL',
            'iso' => '986',
            'name' => 'Real Brésilien',
        )
    );
}

/**
 * Get available countries list
 *
 * @return array
 */
function get_available_countries() {

    return array(
        'ALL' => array(
            'id' => 'ALL',
            'name' => 'TOUS',
        ),
        'ABW' => array(
            'id' => 'ABW',
            'name' => 'Aruba',
        ),
        'AFG' => array(
            'id' => 'AFG',
            'name' => 'Afghanistan',
        ),
        'AGO' => array(
            'id' => 'AGO',
            'name' => 'Angola',
        ),
        'AIA' => array(
            'id' => 'AIA',
            'name' => 'Anguilla',
        ),
        'ALA' => array(
            'id' => 'ALA',
            'name' => 'Ãland îles,',
        ),
        'ALB' => array(
            'id' => 'ALB',
            'name' => 'Albanie',
        ),
        'AND' => array(
            'id' => 'AND',
            'name' => 'Andorre',
        ),
        'ARE' => array(
            'id' => 'ARE',
            'name' => 'Émirats Arabes Unis',
        ),
        'ARG' => array(
            'id' => 'ARG',
            'name' => 'Argentine',
        ),
        'ARM' => array(
            'id' => 'ARM',
            'name' => 'Arménie',
        ),
        'ASM' => array(
            'id' => 'ASM',
            'name' => 'Samoa américaines',
        ),
        'ATA' => array(
            'id' => 'ATA',
            'name' => 'Antarctique',
        ),
        'ATF' => array(
            'id' => 'ATF',
            'name' => 'Terres Autrales française',
        ),
        'ATG' => array(
            'id' => 'ATG',
            'name' => 'Antigua-Et-Barbuda',
        ),
        'AUS' => array(
            'id' => 'AUS',
            'name' => 'Australie',
        ),
        'AUT' => array(
            'id' => 'AUT',
            'name' => 'Autriche',
        ),
        'AZE' => array(
            'id' => 'AZE',
            'name' => 'Azerbaïdjan',
        ),
        'BDI' => array(
            'id' => 'BDI',
            'name' => 'Burundi',
        ),
        'BEL' => array(
            'id' => 'BEL',
            'name' => 'Belgique',
        ),
        'BEN' => array(
            'id' => 'BEN',
            'name' => 'Bénin',
        ),
        'BES' => array(
            'id' => 'BES',
            'name' => 'Bonaire, Saint-Eustache et Saba',
        ),
        'BFA' => array(
            'id' => 'BFA',
            'name' => 'Burkina Faso',
        ),
        'BGD' => array(
            'id' => 'BGD',
            'name' => 'Bangladesh',
        ),
        'BGR' => array(
            'id' => 'BGR',
            'name' => 'Bulgarie',
        ),
        'BHR' => array(
            'id' => 'BHR',
            'name' => 'Bahreïn',
        ),
        'BHS' => array(
            'id' => 'BHS',
            'name' => 'Bahamas',
        ),
        'BIH' => array(
            'id' => 'BIH',
            'name' => 'Bosnie-Herzégovine',
        ),
        'BLM' => array(
            'id' => 'BLM',
            'name' => 'Saint-Kitts-Et-Nevis',
        ),
        'BLR' => array(
            'id' => 'BLR',
            'name' => 'Bélarus',
        ),
        'BLZ' => array(
            'id' => 'BLZ',
            'name' => 'Belize',
        ),
        'BMU' => array(
            'id' => 'BMU',
            'name' => 'Bermudes',
        ),
        'BOL' => array(
            'id' => 'BOL',
            'name' => 'Bolivie',
        ),
        'BRA' => array(
            'id' => 'BRA',
            'name' => 'Brésil',
        ),
        'BRB' => array(
            'id' => 'BRB',
            'name' => 'Barbade',
        ),
        'BRN' => array(
            'id' => 'BRN',
            'name' => 'Brunei Darussalam',
        ),
        'BTN' => array(
            'id' => 'BTN',
            'name' => 'Bhoutan',
        ),
        'BVT' => array(
            'id' => 'BVT',
            'name' => 'Bouvet, île',
        ),
        'BWA' => array(
            'id' => 'BWA',
            'name' => 'Botswana',
        ),
        'CAF' => array(
            'id' => 'CAF',
            'name' => 'Centrafricaine, république',
        ),
        'CAN' => array(
            'id' => 'CAN',
            'name' => 'Canada',
        ),
        'CCK' => array(
            'id' => 'CCK',
            'name' => 'Cocos (Keeling), îles',
        ),
        'CHE' => array(
            'id' => 'CHE',
            'name' => 'Suisse',
        ),
        'CHL' => array(
            'id' => 'CHL',
            'name' => 'Chili',
        ),
        'CHN' => array(
            'id' => 'CHN',
            'name' => 'Chine',
        ),
        'CIV' => array(
            'id' => 'CIV',
            'name' => 'Côte d\'ivoire',
        ),
        'CMR' => array(
            'id' => 'CMR',
            'name' => 'Cameroun',
        ),
        'COD' => array(
            'id' => 'COD',
            'name' => 'Congo, la république démocratique',
        ),
        'COG' => array(
            'id' => 'COG',
            'name' => 'Congo',
        ),
        'COK' => array(
            'id' => 'COK',
            'name' => 'Cook, îles',
        ),
        'COL' => array(
            'id' => 'COL',
            'name' => 'Colombie',
        ),
        'COM' => array(
            'id' => 'COM',
            'name' => 'Comores',
        ),
        'CPV' => array(
            'id' => 'CPV',
            'name' => 'Cap-vert',
        ),
        'CRI' => array(
            'id' => 'CRI',
            'name' => 'Costa Rica',
        ),
        'CUB' => array(
            'id' => 'CUB',
            'name' => 'Cuba',
        ),
        'CUW' => array(
            'id' => 'CUW',
            'name' => 'Curaçao ',
        ),
        'CXR' => array(
            'id' => 'CXR',
            'name' => 'Christmas, îles',
        ),
        'CYM' => array(
            'id' => 'CYM',
            'name' => 'Caïmans, îles',
        ),
        'CYP' => array(
            'id' => 'CYP',
            'name' => 'Chypre',
        ),
        'CZE' => array(
            'id' => 'CZE',
            'name' => 'Tchèque, république',
        ),
        'DEU' => array(
            'id' => 'DEU',
            'name' => 'Allemagne',
        ),
        'DJI' => array(
            'id' => 'DJI',
            'name' => 'Djibouti',
        ),
        'DMA' => array(
            'id' => 'DMA',
            'name' => 'Dominique',
        ),
        'DNK' => array(
            'id' => 'DNK',
            'name' => 'Danemark',
        ),
        'DOM' => array(
            'id' => 'DOM',
            'name' => 'Dominicaine, république',
        ),
        'DZA' => array(
            'id' => 'DZA',
            'name' => 'Algérie',
        ),
        'ECU' => array(
            'id' => 'ECU',
            'name' => 'Équateur',
        ),
        'EGY' => array(
            'id' => 'EGY',
            'name' => 'Égypte',
        ),
        'ERI' => array(
            'id' => 'ERI',
            'name' => 'Érythrée',
        ),
        'ESH' => array(
            'id' => 'ESH',
            'name' => 'Sahara Occidental',
        ),
        'ESP' => array(
            'id' => 'ESP',
            'name' => 'Espagne',
        ),
        'EST' => array(
            'id' => 'EST',
            'name' => 'Estonie',
        ),
        'ETH' => array(
            'id' => 'ETH',
            'name' => 'Éthiopie',
        ),
        'FIN' => array(
            'id' => 'FIN',
            'name' => 'Finlande',
        ),
        'FJI' => array(
            'id' => 'FJI',
            'name' => 'Fidji',
        ),
        'FLK' => array(
            'id' => 'FLK',
            'name' => 'Falkland, îles (Malvinas)',
        ),
        'FRA' => array(
            'id' => 'FRA',
            'name' => 'France',
        ),
        'FRO' => array(
            'id' => 'FRO',
            'name' => 'Féroé, îles',
        ),
        'FSM' => array(
            'id' => 'FSM',
            'name' => 'Micronésie, état fédérés',
        ),
        'GAB' => array(
            'id' => 'GAB',
            'name' => 'Gabon',
        ),
        'GBR' => array(
            'id' => 'GBR',
            'name' => 'Royaume-Uni',
        ),
        'GEO' => array(
            'id' => 'GEO',
            'name' => 'Géorgie',
        ),
        'GGY' => array(
            'id' => 'GGY',
            'name' => 'Guernesey',
        ),
        'GHA' => array(
            'id' => 'GHA',
            'name' => 'Ghana',
        ),
        'GIB' => array(
            'id' => 'GIB',
            'name' => 'Gibraltar',
        ),
        'GIN' => array(
            'id' => 'GIN',
            'name' => 'Guinée',
        ),
        'GLP' => array(
            'id' => 'GLP',
            'name' => 'Guadeloupe',
        ),
        'GMB' => array(
            'id' => 'GMB',
            'name' => 'Gambie',
        ),
        'GNB' => array(
            'id' => 'GNB',
            'name' => 'Guinée-bissau',
        ),
        'GNQ' => array(
            'id' => 'GNQ',
            'name' => 'Guinée équatoriale',
        ),
        'GRC' => array(
            'id' => 'GRC',
            'name' => 'Grèce',
        ),
        'GRD' => array(
            'id' => 'GRD',
            'name' => 'Grenade',
        ),
        'GRL' => array(
            'id' => 'GRL',
            'name' => 'Groenland',
        ),
        'GTM' => array(
            'id' => 'GTM',
            'name' => 'Guatemala',
        ),
        'GUF' => array(
            'id' => 'GUF',
            'name' => 'Guyane française',
        ),
        'GUM' => array(
            'id' => 'GUM',
            'name' => 'Guam',
        ),
        'GUY' => array(
            'id' => 'GUY',
            'name' => 'Guyana',
        ),
        'HKG' => array(
            'id' => 'HKG',
            'name' => 'Hong Kong',
        ),
        'HMD' => array(
            'id' => 'HMD',
            'name' => 'Heard-et-Îles Macdonald',
        ),
        'HND' => array(
            'id' => 'HND',
            'name' => 'Honduras',
        ),
        'HRV' => array(
            'id' => 'HRV',
            'name' => 'Croatie',
        ),
        'HTI' => array(
            'id' => 'HTI',
            'name' => 'Haïti ',
        ),
        'HUN' => array(
            'id' => 'HUN',
            'name' => 'Hongrie',
        ),
        'IDN' => array(
            'id' => 'IDN',
            'name' => 'Indonésie ',
        ),
        'IMN' => array(
            'id' => 'IMN',
            'name' => 'Île de Man',
        ),
        'IND' => array(
            'id' => 'IND',
            'name' => 'Inde',
        ),
        'IOT' => array(
            'id' => 'IOT',
            'name' => 'Océan Indien, Territoire Britannique',
        ),
        'IRL' => array(
            'id' => 'IRL',
            'name' => 'Irlande',
        ),
        'IRN' => array(
            'id' => 'IRN',
            'name' => 'Iran, république Islamique',
        ),
        'IRQ' => array(
            'id' => 'IRQ',
            'name' => 'Iraq',
        ),
        'ISL' => array(
            'id' => 'ISL',
            'name' => 'Islande',
        ),
        'ISR' => array(
            'id' => 'ISR',
            'name' => 'Israël',
        ),
        'ITA' => array(
            'id' => 'ITA',
            'name' => 'Italie',
        ),
        'JAM' => array(
            'id' => 'JAM',
            'name' => 'Jamaïque',
        ),
        'JEY' => array(
            'id' => 'JEY',
            'name' => 'Jersey',
        ),
        'JOR' => array(
            'id' => 'JOR',
            'name' => 'Jordanie',
        ),
        'JPN' => array(
            'id' => 'JPN',
            'name' => 'Japon',
        ),
        'KAZ' => array(
            'id' => 'KAZ',
            'name' => 'Kazakhstan',
        ),
        'KEN' => array(
            'id' => 'KEN',
            'name' => 'Kenya',
        ),
        'KGZ' => array(
            'id' => 'KGZ',
            'name' => 'Kirghizistan',
        ),
        'KHM' => array(
            'id' => 'KHM',
            'name' => 'Cambodge',
        ),
        'KIR' => array(
            'id' => 'KIR',
            'name' => 'Kiribati',
        ),
        'KNA' => array(
            'id' => 'KNA',
            'name' => 'Saint-barthélemy',
        ),
        'KOR' => array(
            'id' => 'KOR',
            'name' => 'Corée',
        ),
        'KWT' => array(
            'id' => 'KWT',
            'name' => 'Koweït',
        ),
        'LAO' => array(
            'id' => 'LAO',
            'name' => 'Lao, république démocratique populaire',
        ),
        'LBN' => array(
            'id' => 'LBN',
            'name' => 'Liban',
        ),
        'LBR' => array(
            'id' => 'LBR',
            'name' => 'Libéria',
        ),
        'LBY' => array(
            'id' => 'LBY',
            'name' => 'Libye',
        ),
        'LCA' => array(
            'id' => 'LCA',
            'name' => 'Sainte-hélène, Ascension et Tritan Da Cunha',
        ),
        'LIE' => array(
            'id' => 'LIE',
            'name' => 'Liechtenstein',
        ),
        'LKA' => array(
            'id' => 'LKA',
            'name' => 'Sri Lanka',
        ),
        'LSO' => array(
            'id' => 'LSO',
            'name' => 'Lesotho',
        ),
        'LTU' => array(
            'id' => 'LTU',
            'name' => 'Lituanie',
        ),
        'LUX' => array(
            'id' => 'LUX',
            'name' => 'Luxembourg',
        ),
        'LVA' => array(
            'id' => 'LVA',
            'name' => 'Lettonie',
        ),
        'MAC' => array(
            'id' => 'MAC',
            'name' => 'Macao',
        ),
        'MAF' => array(
            'id' => 'MAF',
            'name' => 'Saint-Martin(partie française)',
        ),
        'MAR' => array(
            'id' => 'MAR',
            'name' => 'Maroc',
        ),
        'MCO' => array(
            'id' => 'MCO',
            'name' => 'Monaco',
        ),
        'MDA' => array(
            'id' => 'MDA',
            'name' => 'Moldova',
        ),
        'MDG' => array(
            'id' => 'MDG',
            'name' => 'Madagascar',
        ),
        'MDV' => array(
            'id' => 'MDV',
            'name' => 'Maldives',
        ),
        'MEX' => array(
            'id' => 'MEX',
            'name' => 'Mexique',
        ),
        'MHL' => array(
            'id' => 'MHL',
            'name' => 'Marshall, îles',
        ),
        'MKD' => array(
            'id' => 'MKD',
            'name' => 'Macédoine',
        ),
        'MLI' => array(
            'id' => 'MLI',
            'name' => 'Mali',
        ),
        'MLT' => array(
            'id' => 'MLT',
            'name' => 'Malte',
        ),
        'MMR' => array(
            'id' => 'MMR',
            'name' => 'Myanmar',
        ),
        'MNE' => array(
            'id' => 'MNE',
            'name' => 'Monténégro',
        ),
        'MNG' => array(
            'id' => 'MNG',
            'name' => 'Mongolie',
        ),
        'MNP' => array(
            'id' => 'MNP',
            'name' => 'Mariannes du Nord',
        ),
        'MOZ' => array(
            'id' => 'MOZ',
            'name' => 'Mozambique',
        ),
        'MRT' => array(
            'id' => 'MRT',
            'name' => 'Mauritanie',
        ),
        'MSR' => array(
            'id' => 'MSR',
            'name' => 'Montserrat',
        ),
        'MTQ' => array(
            'id' => 'MTQ',
            'name' => 'Martinique',
        ),
        'MUS' => array(
            'id' => 'MUS',
            'name' => 'Maurice',
        ),
        'MWI' => array(
            'id' => 'MWI',
            'name' => 'Malawi',
        ),
        'MYS' => array(
            'id' => 'MYS',
            'name' => 'Malaisie',
        ),
        'MYT' => array(
            'id' => 'MYT',
            'name' => 'Mayotte',
        ),
        'NAM' => array(
            'id' => 'NAM',
            'name' => 'Namibie',
        ),
        'NCL' => array(
            'id' => 'NCL',
            'name' => 'Nouvelle-Calédonie ',
        ),
        'NER' => array(
            'id' => 'NER',
            'name' => 'Niger',
        ),
        'NFK' => array(
            'id' => 'NFK',
            'name' => 'Norfolk',
        ),
        'NGA' => array(
            'id' => 'NGA',
            'name' => 'Nigéria',
        ),
        'NIC' => array(
            'id' => 'NIC',
            'name' => 'Nicaragua',
        ),
        'NIU' => array(
            'id' => 'NIU',
            'name' => 'Niué',
        ),
        'NLD' => array(
            'id' => 'NLD',
            'name' => 'Pays-bas',
        ),
        'NOR' => array(
            'id' => 'NOR',
            'name' => 'Norvège',
        ),
        'NPL' => array(
            'id' => 'NPL',
            'name' => 'Népal',
        ),
        'NRU' => array(
            'id' => 'NRU',
            'name' => 'Nauru ',
        ),
        'NZL' => array(
            'id' => 'NZL',
            'name' => 'Nouvelle-Zélande ',
        ),
        'OMN' => array(
            'id' => 'OMN',
            'name' => 'Oman',
        ),
        'PAK' => array(
            'id' => 'PAK',
            'name' => 'Pakistan',
        ),
        'PAN' => array(
            'id' => 'PAN',
            'name' => 'Panama',
        ),
        'PCN' => array(
            'id' => 'PCN',
            'name' => 'Pitcairn',
        ),
        'PER' => array(
            'id' => 'PER',
            'name' => 'Pérou',
        ),
        'PHL' => array(
            'id' => 'PHL',
            'name' => 'Philippines',
        ),
        'PLW' => array(
            'id' => 'PLW',
            'name' => 'Palaos',
        ),
        'PNG' => array(
            'id' => 'PNG',
            'name' => 'Papouasie-Nouvelle-Guinée',
        ),
        'POL' => array(
            'id' => 'POL',
            'name' => 'Pologne',
        ),
        'PRI' => array(
            'id' => 'PRI',
            'name' => 'Porto Rico',
        ),
        'PRK' => array(
            'id' => 'PRK',
            'name' => 'Corée, république populaire démocratique',
        ),
        'PRT' => array(
            'id' => 'PRT',
            'name' => 'Portugal',
        ),
        'PRY' => array(
            'id' => 'PRY',
            'name' => 'Paraguay',
        ),
        'PSE' => array(
            'id' => 'PSE',
            'name' => 'Palestinien occupé',
        ),
        'PYF' => array(
            'id' => 'PYF',
            'name' => 'Polynésie',
        ),
        'QAT' => array(
            'id' => 'QAT',
            'name' => 'Qatar',
        ),
        'REU' => array(
            'id' => 'REU',
            'name' => 'Réunion ',
        ),
        'ROU' => array(
            'id' => 'ROU',
            'name' => 'Roumanie',
        ),
        'RUS' => array(
            'id' => 'RUS',
            'name' => 'Russie',
        ),
        'RWA' => array(
            'id' => 'RWA',
            'name' => 'Rwanda',
        ),
        'SAU' => array(
            'id' => 'SAU',
            'name' => 'Arabie Saoudite',
        ),
        'SDN' => array(
            'id' => 'SDN',
            'name' => 'Soudan',
        ),
        'SEN' => array(
            'id' => 'SEN',
            'name' => 'Sénégal',
        ),
        'SGP' => array(
            'id' => 'SGP',
            'name' => 'Singapour',
        ),
        'SGS' => array(
            'id' => 'SGS',
            'name' => 'Géorgie du Sud-Et-Les îles Sandwich du Sud',
        ),
        'SHN' => array(
            'id' => 'SHN',
            'name' => 'Saint-Marin',
        ),
        'SJM' => array(
            'id' => 'SJM',
            'name' => 'Svalbard et île Jan Mayen',
        ),
        'SLB' => array(
            'id' => 'SLB',
            'name' => 'Salomon',
        ),
        'SLE' => array(
            'id' => 'SLE',
            'name' => 'Sierra Leone',
        ),
        'SLV' => array(
            'id' => 'SLV',
            'name' => 'El Salvador',
        ),
        'SMR' => array(
            'id' => 'SMR',
            'name' => 'Saint-Martin (partie néerlandaise)',
        ),
        'SOM' => array(
            'id' => 'SOM',
            'name' => 'Somalie',
        ),
        'SPM' => array(
            'id' => 'SPM',
            'name' => 'Saint-Siège',
        ),
        'SRB' => array(
            'id' => 'SRB',
            'name' => 'Serbie',
        ),
        'SSD' => array(
            'id' => 'SSD',
            'name' => 'Soudan Du Sud',
        ),
        'STP' => array(
            'id' => 'STP',
            'name' => 'Sao Tomé-Et-Principe',
        ),
        'SUR' => array(
            'id' => 'SUR',
            'name' => 'Suriname',
        ),
        'SVK' => array(
            'id' => 'SVK',
            'name' => 'Slovaquie',
        ),
        'SVN' => array(
            'id' => 'SVN',
            'name' => 'Slovénie',
        ),
        'SWE' => array(
            'id' => 'SWE',
            'name' => 'Suède',
        ),
        'SWZ' => array(
            'id' => 'SWZ',
            'name' => 'Swaziland',
        ),
        'SXM' => array(
            'id' => 'SXM',
            'name' => 'Saint-Pierre-Et-Miquelon',
        ),
        'SYC' => array(
            'id' => 'SYC',
            'name' => 'Seychelles',
        ),
        'SYR' => array(
            'id' => 'SYR',
            'name' => 'Syrienne, république arabe',
        ),
        'TCA' => array(
            'id' => 'TCA',
            'name' => 'Turks-Et-Caïcos',
        ),
        'TCD' => array(
            'id' => 'TCD',
            'name' => 'Tchad',
        ),
        'TGO' => array(
            'id' => 'TGO',
            'name' => 'Togo',
        ),
        'THA' => array(
            'id' => 'THA',
            'name' => 'Thaïlande ',
        ),
        'TJK' => array(
            'id' => 'TJK',
            'name' => 'Tadjikistan ',
        ),
        'TKL' => array(
            'id' => 'TKL',
            'name' => 'Tokelau',
        ),
        'TKM' => array(
            'id' => 'TKM',
            'name' => 'Turkménistan',
        ),
        'TLS' => array(
            'id' => 'TLS',
            'name' => 'Timor-Leste',
        ),
        'TON' => array(
            'id' => 'TON',
            'name' => 'Tonga',
        ),
        'TTO' => array(
            'id' => 'TTO',
            'name' => 'Trinité-Et-Tobago',
        ),
        'TUN' => array(
            'id' => 'TUN',
            'name' => 'Tunisie',
        ),
        'TUR' => array(
            'id' => 'TUR',
            'name' => 'Turquie',
        ),
        'TUV' => array(
            'id' => 'TUV',
            'name' => 'Tuvalu',
        ),
        'TWN' => array(
            'id' => 'TWN',
            'name' => 'Taïwan',
        ),
        'TZA' => array(
            'id' => 'TZA',
            'name' => 'Tanzanie',
        ),
        'UGA' => array(
            'id' => 'UGA',
            'name' => 'Ouganda',
        ),
        'UKR' => array(
            'id' => 'UKR',
            'name' => 'Ukraine',
        ),
        'UMI' => array(
            'id' => 'UMI',
            'name' => 'Îles mineures éloignées des États-Unis',
        ),
        'URY' => array(
            'id' => 'URY',
            'name' => 'Uruguay',
        ),
        'USA' => array(
            'id' => 'USA',
            'name' => 'États-Unis ',
        ),
        'UZB' => array(
            'id' => 'UZB',
            'name' => 'Ouzbékistan ',
        ),
        'VAT' => array(
            'id' => 'VAT',
            'name' => 'Saint-Vincent-Et-Les Grenadines',
        ),
        'VCT' => array(
            'id' => 'VCT',
            'name' => 'Sainte-Lucie',
        ),
        'VEN' => array(
            'id' => 'VEN',
            'name' => 'Venezuela',
        ),
        'VGB' => array(
            'id' => 'VGB',
            'name' => 'Îles vierges britaniques',
        ),
        'VIR' => array(
            'id' => 'VIR',
            'name' => 'Îles vierges des États-Unis',
        ),
        'VNM' => array(
            'id' => 'VNM',
            'name' => 'Vietnam',
        ),
        'VUT' => array(
            'id' => 'VUT',
            'name' => 'Vanuatu',
        ),
        'WLF' => array(
            'id' => 'WLF',
            'name' => 'Wallis et Futuna',
        ),
        'WSM' => array(
            'id' => 'WSM',
            'name' => 'Samoa',
        ),
        'YEM' => array(
            'id' => 'YEM',
            'name' => 'Yémen ',
        ),
        'ZAF' => array(
            'id' => 'ZAF',
            'name' => 'Afrique Du Sud',
        ),
        'ZMB' => array(
            'id' => 'ZMB',
            'name' => 'Zambie',
        ),
        'ZWE' => array(
            'id' => 'ZWE',
            'name' => 'Zimbabwe',
        )
    );
}
