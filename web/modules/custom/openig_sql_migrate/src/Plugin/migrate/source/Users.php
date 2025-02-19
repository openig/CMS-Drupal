<?php

namespace Drupal\openig_sql_migrate\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * Minimalistic example for a SqlBase source plugin.
 *
 * @MigrateSource(
 *   id = "users",
 *   source_module = "openig_sql_migrate",
 * )
 */
class Users extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('auth_user', 'u');
    $query->distinct();
    $query->leftJoin('idgo_admin_profile','p', 'p.user_id = u.id');
    $query->leftJoin('idgo_admin_organisation','o', 'o.id = p.organisation_id');

    $query->fields('u', [
        'id',
        'username',
        'email',
        'last_login',
        'is_active',
        'is_staff',
        'first_name',
        'last_name',
        'date_joined',
    ]);
    $query->addField('p', 'phone');
    $query->addField('p', 'is_admin');
    $query->addField('p', 'organisation_id');

    // Exclude neogeo
    $query->condition('u.email', '%@neogeo%', 'NOT LIKE');
    $query->condition('u.username', 'admin', 'NOT LIKE');
   
    $usernames = [
      'agedi_cadastre',
      'cmartyagedi',
      'gwenael',
      'rdeletage',
      'omaratuech',
      'agencedespyrenees',
      'jdesboeufs',
      'anct_pcrs',
      'lucafacciano',
      'jsbailly',
      'celia_bsnpal',
      'mdieff2',
      'fportet',
      'cyprien_v',
      'hdurand',
      'violaine',
      'emiliearec',
      'aquafontedit',
      'h-quinette66',
      'canal_thuir',
      'chugodot',
      'barou66720',
      'aseaude',
      'asso_bv_ta',
      'afpgp_po',
      'iboulet',
      'vchoppin',
      'amayis',
      'alexandraphilip',
      'ncaparros',
      'h_dagneau',
      'lisegrolleau',
      'ninalaroyenne',
      'dretourna',
      'marc-stx',
      'lbigourdan',
      'sherviou',
      'nourredine_idir',
      'eme',
      'florian_allary',
      'ineghina',
      'bets',
      'agripv',
      'balouin',
      'fournely',
      'bmonod',
      'epal',
      'camillejourdanbrli',
      'btinel',
      'canokathy',
      'lalvernhe',
      'bohere',
      'dbrazeau',
      'lcadene',
      'yfont',
      'virginie_lee',
      'cmarch',
      'hoelhaddouni',
      'fxhalle',
      'xasudre',
      'sgreliche',
      'ccam',
      'sigccbta',
      'sigccbta',
      'cc-',
      'les_plantiers',
      'bremy',
      'jfraulet',
      'avidal48',
      'jr66',
      'ndelaval',
      'rlagoin',
      'bastien',
      'sophie_go',
      'stolar',
      'jmiossec',
      'gsouchon',
      'pchaud',
      'gaelle-ladomitienne',
      'lpoblador',
      'mcatala',
      'rralite',
      'jtailhan',
      'jramond',
      'ccorreia',
      'cdauce',
      'cbaillou',
      'sbouetroussel',
      'efoulquier',
      'slathuillere',
      'jmilliot',
      'omaillard',
      'tguilbert',
      'gandrieu',
      'jbenkemoun',
      'ccphg_philippe',
      'lblum',
      'fverloo',
      'fverloo',
      'dcornillon',
      'glaurant',
      'saintchinian',
      'ccsr_sig',
      'cctcam',
      'sandrinec',
      'jaycreb',
      'service_sig',
      'baranoff',
      'ccvallespir',
      'cloiseau',
      'fouvet',
      'lsaintgeniez',
      'matthieu',
      'hugo-b',
      'aloisfca11',
      'cazade',
      'demessazf',
      'jouliei',
      'leonorpm',
      'petitjc',
      'sostrel',
      'antoineca66',
      'cirrcalvin',
      'cci30',
      'gcausera',
      'gaetanprulhiere',
      'ej-ccrlcm',
      'ansignan',
      'brignon',
      'jcrodriguez',
      'dstcalv',
      'caramany',
      'dst',
      'communefougaron',
      'respaut',
      'lauras',
      'fpallet',
      'jhanke',
      'urba_millas',
      'faed',
      'delaval-n',
      'mairieprugnanes',
      'rivesaltes66',
      'saint-ambroix',
      'cfabre',
      'cedric',
      'rfresne',
      'maire',
      'fgras',
      'saint-cyprien',
      'mairie-sthippo',
      'larroche',
      'fpallet-saumane',
      'gbobe',
      'mairiesournia',
      'sumenemairie',
      'recolin',
      'recolin',
      'brunosol',
      'nloubet',
      'fenouillet66',
      'gchiroleu',
      'mairielevivier',
      'amelie',
      'stephanerolle',
      'antoine_delarue',
      'ncatala',
      'slg66',
      'vandeputte',
      'vbarbet',
      'ddtm_66',
      'bmaranges',
      'arthur_l',
      'combes-b',
      'ofroidefon',
      'lesage_k',
      'cd31_ft',
      'benjaminmarnat',
      'swatremez',
      'mbonrepaux',
      'luc_guiavarch',
      'agn7s',
      'j_b',
      'margot',
      'sdelorme',
      'depoix',
      'gonzalez',
      'julie_rondole',
      'fanny',
      'caroline_fabre',
      'romiguierela',
      'floriane_arnal',
      'cchastagnol',
      'ogarcia',
      'cgasc',
      'yjazeron',
      'alafaye',
      'abniang',
      'yrequi',
      'aconan',
      'hulin',
      'fpouyenne',
      'iboulabiar',
      'ymr',
      'vbouvet',
      'departement_gard',
      'lcourret',
      'fd_dptgard',
      'tft-30',
      'galien_m',
      '1796',
      'olive',
      'obonnefon',
      'cdujardin',
      'flaby',
      'thierry_line',
      'firminl',
      'philippe_hans',
      'lugliera',
      'cmontoya',
      'dolev',
      'cabirol',
      'jchailloux',
      '002',
      'akerr',
      'nnouviaire',
      'ellipsig',
      'kevinsannac',
      'quentin2113',
      'jedossa',
      'dlyszczarz',
      'lcrotet',
      'eptb-ardeche',
      'papi_ardeche',
      'tech-affluents',
      'ameunier',
      'pnegre',
      'pnegre',
      'hfabrega',
      'kadoul',
      'eptbvv',
      'jdevictor',
      'fdai34',
      'emingo',
      'aherault',
      'fplg',
      'foretprivee09',
      'thierry',
      'ealgans',
      'sfort',
      'storm',
      'ecandaele',
      'jgourmelon',
      'tmonbrun',
      'jarrassier',
      'bouyer',
      'dlaurent',
      'hydriad',
      'kazemar',
      'jefbehm',
      'thomas_idgeo',
      'iemn',
      'alice_argentero',
      'zchastin',
      'laco504c',
      'graveline',
      'plagacherie',
      'mnmistou',
      'fvinatier',
      'phil976',
      'sbourdeau',
      'lgaudiot',
      'flandais',
      'lmuscarnera',
      'smuscarnera',
      'racanierejonathan',
      'rxlacroix',
      'vdelbar',
      'bastien_latelescop',
      'lavionjaune',
      'clseard',
      'esole',
      'sorlinraphael',
      'ecologistes_euziere',
      'efournier',
      'jyg',
      'jbarthes',
      'clement_g',
      'anthony',
      'fayolle',
      'jochump',
      'amelie-labbe',
      'lachhab',
      'amarc',
      'as_muepu',
      'vronzetti',
      'cvalot',
      'naos',
      'joelkamdoum',
      'daouda2024',
      'guieysse',
      'nimes_metropole',
      'nicolas',
      'fmoll',
      'mgseptfonds',
      'nlandes',
      'michelbernard',
      'dcarauxgarson',
      'fdeblomac',
      'jgouin',
      'plem',
      'bmonthubert',
      'bsegala',
      'ebalaye',
      'gypaetusbarbatus',
      'j-f_raymond',
      'mariobon',
      'noelcarreno',
      'lara-mh',
      'ldepontailler',
      'mabinisti',
      'clement-ptcm',
      'pefc-occitanie',
      'fcarvalhais',
      'catinaud',
      'fporte',
      'drouffart',
      'furville',
      'yohannpetr',
      'afesquetpnrcf',
      'jordan',
      'frichart',
      'ayard',
      'lbejanin',
      'sgargiulo',
      'elasar',
      'pmignon',
      'virgile',
      'johanna',
      'camilledebrock',
      'carole_desmarais',
      'drajkowski',
      'clairefaroux',
      'hugolecharpentier',
      'pageau',
      'soulie_h',
      'mterrier',
      'dvazelle',
      'loeiz',
      'christophe-hurson',
      'janoel',
      'gaetan_rp',
      'rmagana',
      'mchevignon',
      'lefrancois',
      'maelle_decherf',
      'vreynes',
      'sde09-sg',
      'ffalcon',
      'rsdis30',
      'vaginay',
      'bmonteverde',
      'rgutton',
      'nmoyroud',
      'hbessiere',
      'adufaure',
      'samalens',
      'kevinberva',
      'yroussel',
      'fgasc1',
      'vroux',
      'dconstant',
      'cesmat-smbt',
      'jproussillon',
      'cboesch',
      'ftouletblanquet',
      'smbvr',
      'cbrochier',
      'patrick',
      'gmombertrand',
      'lloukani',
      'tclemencet',
      'c_helissey',
      'dmunck',
      'shape',
      'tmayrand',
      'jeremy_serieye',
      'csoulie',
      'jleguern',
      'tportier',
      'abartolo',
      'ericspinosa',
      'zeroili',
      'mathilde2sw',
      'juliettedomage',
      'keller',
      'nicoclarin',
      'delpouxn',
      'pmedina',
      'pmsyaden',
      'sydeel--',
      'acastagnet',
      'e-roujon',
      'hlegueil',
      'sbl34340',
      'ugolin-pnr-aubrac',
      'cloe',
      'mariehst',
      'nicolaslebloispnraubrac',
      'jorispesche',
      'smgas',
      'ava_h',
      'mag_mari',
      'smbvtam',
      'smpb',
      'smixtetpcf',
      'smld',
      'gnadal',
      'spages',
      'cmurgue',
      'mdaragon',
      'tigeo',
      'viguierlionel',
      'cylex',
      'ucic',
      'cdaste',
      'aigo',
      'spont',
      'aurelien_ayme',
      'luciejckr',
      'coco',
      'lparadis',
      'smeetsn',
      'up34',
      'ychristolveillet',
      'inesc',
      'maximedumont',
      'quentin',
      'florent_r',
      'olivier_t',
      'leo_vanel',
      'matalm',
      'y_anjoubault',
      'arsic',
      'cbadouel',
      'sandrabarantal',
      'mairiesaintprive',
      'maribari',
      'lbertrand',
      'jean-paul_bessiere',
      'tercia',
      'gaxieu',
      'cascales30',
      'acollin',
      'jcoutaz',
      'declochez',
      'dejouxjf',
      'sdespaux',
      'vdouarre',
      'tdh96',
      'doumbsam',
      'vdd',
      'sez',
      'y_ferricelli',
      'theofinocchiaro',
      'gil',
      'mgca12',
      'rhermenge',
      'novadapt',
      'fredericlabouyrie',
      'hlavrut',
      'rlc',
      'glemasle',
      'bureaumh2o',
      'clementl',
      'marie_meletopoulos',
      'sme',
      'acm',
      'benoit-mutius',
      'choulyouf',
      'dyo',
      'rrignani',
      'gilrodrigues',
      'sigartifex',
      'maximus34560',
      'georges',
      'theisen',
      'bastien46340',
      'nvila',
      'pvincon',     
    ];
  
    // Exclude users
    $query->condition('u.username', $usernames , 'IN');
    // $query->range(0, 1); // limit to 1, debug only
    
    // Do something with each $record

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
/*
    return [
        'id',
        'legal_name',
        'slug',
        'email',
        'website',
        'description',
        'address',
        'postcode',
        'city',
        'phone',
        'organisation_type_id',
        'organisation_type_name',
        'siren',
        'is_active',
        'is_crige_partner'
    ];
*/
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'id' => [
        'type' => 'integer',
        'alias' => 'u',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {

    // Link de l'organisation vers les structures déjà importés
    $database = \Drupal::database();
    $query = $database->query("SELECT sourceid1, destid1 FROM {migrate_map_structures} WHERE sourceid1 = '".intval($row->getSourceProperty('organisation_id'))."'");
    $results = $query->fetchAllKeyed();

    foreach ($results as $key => $value) {
      if ($key == $row->getSourceProperty('organisation_id')) {
        $row->setSourceProperty('organisation_id', $value);
      }
    }

    // Correction des dates
    $last_login = \DateTime::createFromFormat('Y-m-d H:i:s.uT', $row->getSourceProperty('last_login'));
    if($last_login) $row->setSourceProperty('last_login', $last_login->format('U'));
    
    $date_joined = \DateTime::createFromFormat('Y-m-d H:i:s.uT', $row->getSourceProperty('date_joined'));
    if($date_joined) $row->setSourceProperty('date_joined', $date_joined->format('U'));


    return parent::prepareRow($row);
  }
}

