<?php

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Stripe;
use Stripe\Error;

$myEmail = EMAIL;
$ecEmail = EC_EMAIL;

$app->match('/', function () use ($app) {
    return $app->redirect($app['url_generator']->generate('login'));
})->bind('homepage');

$app->match('/central/orders', function(Request $request) use ($app, $myEmail, $ecEmail){
    $orders = $app['db']->fetchAll('
    SELECT * FROM ismartbrowse_orders;
    ');

    $form = array();
    $builder = $app['form.factory']->createBuilder('form');
    foreach ($orders as $oneOrder) {
        $form[] = $builder
            ->add('trackingNumber', 'text')
            ->add('shipped', 'choice', array(
                'expanded' => true,
                'multiple' => true,
                'choices_as_values' => false,
                'choices' => array('1' => ' ')
            ))
            ->add('orderNumber', 'hidden', array(
                'data' => $oneOrder['id'],
                'mapped' => false,
            ))
            ->getForm()->createView();
    }
    if ($request->request->get('orderNumber') || $request->request->get('trackingNumber') || $request->request->get('email')) {
        $order = array(
            'orderNumber' => intval($request->request->get('orderNumber')),
            'trackingNumber' => $request->request->get('trackingNumber'),
            'email' => $request->request->get('email'),
        );
        $updateQuery = "UPDATE ismartbrowse_orders SET trackingNumber = '" . $order['trackingNumber'] .  "', shipped = 1 WHERE id = " . $order['orderNumber'];
        $result = $app['db']->executeUpdate($updateQuery, array($order['trackingNumber'], 1, $order['orderNumber']));
        if ($result) {
            $id = $request->request->get('orderNumber');
            $customerOrder = $app['db']->fetchAssoc('SELECT * FROM ismartbrowse_orders WHERE id = ' . $id);
            $transporter = Swift_SmtpTransport::newInstance(EMAIL_HOST, EMAIL_PORT, 'ssl')
                ->setUsername($myEmail)
                ->setPassword(EMAIL_PASS);

//                    Create Twig Template
            $template = $app['twig']->render('upsTrackingInformation.html.twig', array(
                                            'customerOrder' => $customerOrder,
                                            'ecEmail' => $ecEmail,
            ));

//                    Create mailer
            $mailer = \Swift_Mailer::newInstance($transporter);
            $message = Swift_Message::newInstance('Test')
                ->setFrom(array($myEmail))
                ->setTo(array($customerOrder['email'], $myEmail))
                ->setSubject('Your UPS Tracking Information')
                ->setBody($template, 'text/html');

            $mailer->send($message);
            return $app->json($result, 201);
        }
    }

    return $app['twig']->render('central_orders.html.twig', array('orders' => $orders, 'form' => $form));
});

$app->get('/login', function (Request $request) use ($app) {
    $form = $app['form.factory']->createBuilder('form')
        ->add(
            'username',
            'text',
            array(
                'label' => 'Username',
                'data' => $app['session']->get('_security.last_username')
            )
        )
        ->add('password', 'password', array('label' => 'Password'))
        ->getForm();

    return $app['twig']->render('login.html.twig', array(
        'form'  => $form->createView(),
        'error' => $app['security.last_error']($request),
    ));
})->bind('login');

$app->match('/purchase', function(Request $request) use($app, $ecEmail, $myEmail){
    $publicKey = PUBLIC_KEY;
    $privateKey = PRIVATE_KEY;
    $builder = $app['form.factory']->createBuilder('form');
    $gender = array('m' => 'Male', 'f' => 'Female');
    $countries = array(
        "AF" => "Afghanistan",
        "AX" => "Åland Islands",
        "AL" => "Albania",
        "DZ" => "Algeria",
        "AS" => "American Samoa",
        "AD" => "Andorra",
        "AO" => "Angola",
        "AI" => "Anguilla",
        "AQ" => "Antarctica",
        "AG" => "Antigua and Barbuda",
        "AR" => "Argentina",
        "AM" => "Armenia",
        "AW" => "Aruba",
        "AU" => "Australia",
        "AT" => "Austria",
        "AZ" => "Azerbaijan",
        "BS" => "Bahamas",
        "BH" => "Bahrain",
        "BD" => "Bangladesh",
        "BB" => "Barbados",
        "BY" => "Belarus",
        "BE" => "Belgium",
        "BZ" => "Belize",
        "BJ" => "Benin",
        "BM" => "Bermuda",
        "BT" => "Bhutan",
        "BO" => "Bolivia",
        "BA" => "Bosnia and Herzegovina",
        "BW" => "Botswana",
        "BV" => "Bouvet Island",
        "BR" => "Brazil",
        "IO" => "British Indian Ocean Territory",
        "BN" => "Brunei Darussalam",
        "BG" => "Bulgaria",
        "BF" => "Burkina Faso",
        "BI" => "Burundi",
        "KH" => "Cambodia",
        "CM" => "Cameroon",
        "CA" => "Canada",
        "CV" => "Cape Verde",
        "KY" => "Cayman Islands",
        "CF" => "Central African Republic",
        "TD" => "Chad",
        "CL" => "Chile",
        "CN" => "China",
        "CX" => "Christmas Island",
        "CC" => "Cocos (Keeling) Islands",
        "CO" => "Colombia",
        "KM" => "Comoros",
        "CG" => "Congo",
        "CD" => "Congo, The Democratic Republic of The",
        "CK" => "Cook Islands",
        "CR" => "Costa Rica",
        "CI" => "Cote D'ivoire",
        "HR" => "Croatia",
        "CU" => "Cuba",
        "CY" => "Cyprus",
        "CZ" => "Czech Republic",
        "DK" => "Denmark",
        "DJ" => "Djibouti",
        "DM" => "Dominica",
        "DO" => "Dominican Republic",
        "EC" => "Ecuador",
        "EG" => "Egypt",
        "SV" => "El Salvador",
        "GQ" => "Equatorial Guinea",
        "ER" => "Eritrea",
        "EE" => "Estonia",
        "ET" => "Ethiopia",
        "FK" => "Falkland Islands (Malvinas)",
        "FO" => "Faroe Islands",
        "FJ" => "Fiji",
        "FI" => "Finland",
        "FR" => "France",
        "GF" => "French Guiana",
        "PF" => "French Polynesia",
        "TF" => "French Southern Territories",
        "GA" => "Gabon",
        "GM" => "Gambia",
        "GE" => "Georgia",
        "DE" => "Germany",
        "GH" => "Ghana",
        "GI" => "Gibraltar",
        "GR" => "Greece",
        "GL" => "Greenland",
        "GD" => "Grenada",
        "GP" => "Guadeloupe",
        "GU" => "Guam",
        "GT" => "Guatemala",
        "GG" => "Guernsey",
        "GN" => "Guinea",
        "GW" => "Guinea-bissau",
        "GY" => "Guyana",
        "HT" => "Haiti",
        "HM" => "Heard Island and Mcdonald Islands",
        "VA" => "Holy See (Vatican City State)",
        "HN" => "Honduras",
        "HK" => "Hong Kong",
        "HU" => "Hungary",
        "IS" => "Iceland",
        "IN" => "India",
        "ID" => "Indonesia",
        "IR" => "Iran, Islamic Republic of",
        "IQ" => "Iraq",
        "IE" => "Ireland",
        "IM" => "Isle of Man",
        "IL" => "Israel",
        "IT" => "Italy",
        "JM" => "Jamaica",
        "JP" => "Japan",
        "JE" => "Jersey",
        "JO" => "Jordan",
        "KZ" => "Kazakhstan",
        "KE" => "Kenya",
        "KI" => "Kiribati",
        "KP" => "Korea, Democratic People's Republic of",
        "KR" => "Korea, Republic of",
        "KW" => "Kuwait",
        "KG" => "Kyrgyzstan",
        "LA" => "Lao People's Democratic Republic",
        "LV" => "Latvia",
        "LB" => "Lebanon",
        "LS" => "Lesotho",
        "LR" => "Liberia",
        "LY" => "Libyan Arab Jamahiriya",
        "LI" => "Liechtenstein",
        "LT" => "Lithuania",
        "LU" => "Luxembourg",
        "MO" => "Macao",
        "MK" => "Macedonia, The Former Yugoslav Republic of",
        "MG" => "Madagascar",
        "MW" => "Malawi",
        "MY" => "Malaysia",
        "MV" => "Maldives",
        "ML" => "Mali",
        "MT" => "Malta",
        "MH" => "Marshall Islands",
        "MQ" => "Martinique",
        "MR" => "Mauritania",
        "MU" => "Mauritius",
        "YT" => "Mayotte",
        "MX" => "Mexico",
        "FM" => "Micronesia, Federated States of",
        "MD" => "Moldova, Republic of",
        "MC" => "Monaco",
        "MN" => "Mongolia",
        "ME" => "Montenegro",
        "MS" => "Montserrat",
        "MA" => "Morocco",
        "MZ" => "Mozambique",
        "MM" => "Myanmar",
        "NA" => "Namibia",
        "NR" => "Nauru",
        "NP" => "Nepal",
        "NL" => "Netherlands",
        "AN" => "Netherlands Antilles",
        "NC" => "New Caledonia",
        "NZ" => "New Zealand",
        "NI" => "Nicaragua",
        "NE" => "Niger",
        "NG" => "Nigeria",
        "NU" => "Niue",
        "NF" => "Norfolk Island",
        "MP" => "Northern Mariana Islands",
        "NO" => "Norway",
        "OM" => "Oman",
        "PK" => "Pakistan",
        "PW" => "Palau",
        "PS" => "Palestinian Territory, Occupied",
        "PA" => "Panama",
        "PG" => "Papua New Guinea",
        "PY" => "Paraguay",
        "PE" => "Peru",
        "PH" => "Philippines",
        "PN" => "Pitcairn",
        "PL" => "Poland",
        "PT" => "Portugal",
        "PR" => "Puerto Rico",
        "QA" => "Qatar",
        "RE" => "Reunion",
        "RO" => "Romania",
        "RU" => "Russian Federation",
        "RW" => "Rwanda",
        "SH" => "Saint Helena",
        "KN" => "Saint Kitts and Nevis",
        "LC" => "Saint Lucia",
        "PM" => "Saint Pierre and Miquelon",
        "VC" => "Saint Vincent and The Grenadines",
        "WS" => "Samoa",
        "SM" => "San Marino",
        "ST" => "Sao Tome and Principe",
        "SA" => "Saudi Arabia",
        "SN" => "Senegal",
        "RS" => "Serbia",
        "SC" => "Seychelles",
        "SL" => "Sierra Leone",
        "SG" => "Singapore",
        "SK" => "Slovakia",
        "SI" => "Slovenia",
        "SB" => "Solomon Islands",
        "SO" => "Somalia",
        "ZA" => "South Africa",
        "GS" => "South Georgia and The South Sandwich Islands",
        "ES" => "Spain",
        "LK" => "Sri Lanka",
        "SD" => "Sudan",
        "SR" => "Suriname",
        "SJ" => "Svalbard and Jan Mayen",
        "SZ" => "Swaziland",
        "SE" => "Sweden",
        "CH" => "Switzerland",
        "SY" => "Syrian Arab Republic",
        "TW" => "Taiwan, Province of China",
        "TJ" => "Tajikistan",
        "TZ" => "Tanzania, United Republic of",
        "TH" => "Thailand",
        "TL" => "Timor-leste",
        "TG" => "Togo",
        "TK" => "Tokelau",
        "TO" => "Tonga",
        "TT" => "Trinidad and Tobago",
        "TN" => "Tunisia",
        "TR" => "Turkey",
        "TM" => "Turkmenistan",
        "TC" => "Turks and Caicos Islands",
        "TV" => "Tuvalu",
        "UG" => "Uganda",
        "UA" => "Ukraine",
        "AE" => "United Arab Emirates",
        "GB" => "United Kingdom",
        "US" => "United States",
        "UM" => "United States Minor Outlying Islands",
        "UY" => "Uruguay",
        "UZ" => "Uzbekistan",
        "VU" => "Vanuatu",
        "VE" => "Venezuela",
        "VN" => "Viet Nam",
        "VG" => "Virgin Islands, British",
        "VI" => "Virgin Islands, U.S.",
        "WF" => "Wallis and Futuna",
        "EH" => "Western Sahara",
        "YE" => "Yemen",
        "ZM" => "Zambia",
        "ZW" => "Zimbabwe");

    $states = array(
        'AL'=>'Alabama',
        'AK'=>'Alaska',
        'AZ'=>'Arizona',
        'AR'=>'Arkansas',
        'CA'=>'California',
        'CO'=>'Colorado',
        'CT'=>'Connecticut',
        'DE'=>'Delaware',
        'DC'=>'District of Columbia',
        'FL'=>'Florida',
        'GA'=>'Georgia',
        'HI'=>'Hawaii',
        'ID'=>'Idaho',
        'IL'=>'Illinois',
        'IN'=>'Indiana',
        'IA'=>'Iowa',
        'KS'=>'Kansas',
        'KY'=>'Kentucky',
        'LA'=>'Louisiana',
        'ME'=>'Maine',
        'MD'=>'Maryland',
        'MA'=>'Massachusetts',
        'MI'=>'Michigan',
        'MN'=>'Minnesota',
        'MS'=>'Mississippi',
        'MO'=>'Missouri',
        'MT'=>'Montana',
        'NE'=>'Nebraska',
        'NV'=>'Nevada',
        'NH'=>'New Hampshire',
        'NJ'=>'New Jersey',
        'NM'=>'New Mexico',
        'NY'=>'New York',
        'NC'=>'North Carolina',
        'ND'=>'North Dakota',
        'OH'=>'Ohio',
        'OK'=>'Oklahoma',
        'OR'=>'Oregon',
        'PA'=>'Pennsylvania',
        'RI'=>'Rhode Island',
        'SC'=>'South Carolina',
        'SD'=>'South Dakota',
        'TN'=>'Tennessee',
        'TX'=>'Texas',
        'UT'=>'Utah',
        'VT'=>'Vermont',
        'VA'=>'Virginia',
        'WA'=>'Washington',
        'WV'=>'West Virginia',
        'WI'=>'Wisconsin',
        'WY'=>'Wyoming',
    );

    $form = $builder
                ->add('sameAsShipping', 'choice', array(
                    'expanded'  =>  true,
                    'multiple'  =>  true,
                    'choices'   =>  array(
                        '1'   =>  'Billing is same as shipping'
                    )
                ))
                ->add('payment', 'choice', array(
                    'expanded'  =>  true,
                    'multiple'  =>  false,
                    'choices'   =>  array(
                        '1 pair for $7' =>  '1',
                        '2 pairs for $10' =>  '2',
                    ),
                    'label'     =>  'Select your purchase',
                    'choices_as_values' =>  true,
                ))
                ->add('email', 'email', array(
                    'label'      => 'Email',
                    'constraints' => new Assert\NotBlank(),
                    'attr'       => array('placeholder' => 'Email')
                ))
                ->add('gender', 'choice', array(
                    'choices'  => $gender,
                    'multiple' => false,
                    'expanded' => true
                ))
                ->add('firstName', 'text', array(
                    'label' => 'First Name',
                    'constraints' => new Assert\NotBlank(),
                    'attr'        => array('placeholder' => 'First Name')
                ))
                ->add('lastName', 'text', array(
                    'label' => 'Last Name',
                    'constraints' => new Assert\NotBlank(),
                    'attr'        => array('placeholder' => 'Last Name')
                ))
                ->add('address1Billing', 'text', array(
                    'label' => 'Address 1',
                    'constraints' => new Assert\NotBlank(),
                    'attr'        => array('placeholder' => 'Address 1')
                ))
                ->add('address2Billing', 'text', array(
                    'label' => 'Address 2',
                    'constraints' => new Assert\NotBlank(),
                    'attr'        => array('placeholder' => 'Apt Number')
                ))
                ->add('zipBilling', 'text', array(
                    'label' => 'Zip',
                    'constraints' => new Assert\NotBlank(),
                    'attr'        => array('placeholder' => 'Zip')
                ))
                ->add('cityBilling', 'text', array(
                    'label' => 'City',
                    'constraints' => new Assert\NotBlank(),
                    'attr'        => array('placeholder' => 'City')
                ))
                ->add('stateBilling', 'choice', array(
                    'choices' => $states,
                    'empty_data' => 'Choose your state',
                    'constraints' => new Assert\NotBlank(),
                    'multiple' => false,
                    'expanded' => false,
                    'label' => 'State',
                ))
                ->add('countryBilling', 'choice', array(
                    'label' => 'Country',
                    'choices' => $countries,
                    'constraints' => new Assert\NotBlank(),
                    'multiple' => false,
                    'expanded' => false
                ))
                ->add('quantity', 'text', array(
                    'label' => 'Quantity',
                    'constraints' => new Assert\NotBlank(),
                    'attr'        => array('placeholder' => 'Quantity')
                ))
                ->add('address1Shipping', 'text', array(
                    'label' => 'Address 1',
                    'attr'          =>  array('placeholder' => 'Address 1'),
                ))
                ->add('address2Shipping', 'text', array(
                    'label' => 'Address 2',
                    'attr'        => array('placeholder' => 'Apt Number')
                ))
                ->add('zipShipping', 'text', array(
                    'label' => 'Zip',
                    'attr'        => array('placeholder' => 'Zip')
                ))
                ->add('cityShipping', 'text', array(
                    'label' => 'City',
                    'attr'        => array('placeholder' => 'City')
                ))
                ->add('stateShipping', 'choice', array(
                    'choices' => $states,
                    'empty_data' => 'Choose your state',
                    'multiple' => false,
                    'expanded' => false
                ))
                ->add('countryShipping', 'choice', array(
                    'choices' => $countries,
                    'multiple' => false,
                    'expanded' => false
                ))
                ->add('publicKey', 'hidden', array(
                    'data' =>  $publicKey,
                ))
                ->add('submit', 'submit')
                ->getForm();

    $form->handleRequest($request);
    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            $errors = array();
            $stripeToken = array();
            $data = $request->request->get('form');
            $isSameAsShipping = ($data['sameAsShipping'][0] == '1') ? false : true;
            if ($isSameAsShipping) {
                $address1Shipping = $data['address1Shipping'];
                $address2Shipping = $data['address2Shipping'];
                $zipShipping = $data['zipShipping'];
                $cityShipping = $data['cityShipping'];
                $stateShipping = $data['stateShipping'];
                $countryShipping = $data['countryShipping'];
                $sameAsShipping = 1;
            } else {
                $address1Shipping = null;
                $address2Shipping = null;
                $zipShipping = null;
                $cityShipping = null;
                $stateShipping = null;
                $countryShipping = null;
                $sameAsShipping = 0;
            }
            $token = $request->request->get('stripeToken');
            $payment = $data['payment'];
            $amount = 10;
            if ($payment == '1') {
                $amount = 7;
            }
            if (isset($token)) {
                if ($token == $stripeToken['token'] = $app['session']->get('stripeToken')) {
                    $errors['token'] = 'You have apparently re-submitted the form.';
                } else {
                    $app['session']->set('stripeToken', array('token' => $token));
                }
            } else {
                $errors['token'] = 'The order cannot be processed. You have not been charged.
                            Please confirm that you have JavaScript enabled and try again.';
            }

            if (empty($errors)) {
                try {
                    Stripe\Stripe::setApiKey($privateKey);

                    $customerEmail = $data['email'];
                    $emailArray = array($customerEmail, $ecEmail, $myEmail);

                    $charge = Stripe\Charge::create(array(
                        "amount"        =>  $amount*100*$data['quantity'],
                        "currency"      =>  "usd",
                        "source"        =>  $token,
                        "description"   =>  $data['email'],
                        'receipt_email' =>  $data['email'],
                    ));

                    if ($charge->paid == true) {

                        $stmt = $app['db']->prepare('
                                  INSERT INTO ismartbrowse_orders
                                  (email, firstName, lastName, gender, address1Billing, address2Billing, zipBilling, cityBilling, stateBilling, countryBilling, address1Shipping, address2Shipping, zipShipping, cityShipping, stateShipping, countryShipping, quantity, paymentChoice, sameAsShipping)
                                 VALUES (:email, :firstName, :lastName, :gender, :address1Billing, :address2Billing, :zipBilling, :cityBilling, :stateBilling, :countryBilling, :address1Shipping, :address2Shipping, :zipShipping, :cityShipping, :stateShipping, :countryShipping, :quantity, :payment, :sameAsShipping)');

                        $stmt->bindParam(':email', $data['email']);
                        $stmt->bindParam(':firstName', $data['firstName']);
                        $stmt->bindParam(':lastName', $data['lastName']);
                        $stmt->bindParam(':gender', $data['gender']);
                        $stmt->bindParam(':address1Billing', $data['address1Billing']);
                        $stmt->bindParam(':address2Billing', $data['address2Billing']);
                        $stmt->bindParam(':zipBilling', $data['zipBilling']);
                        $stmt->bindParam(':cityBilling', $data['cityBilling']);
                        $stmt->bindParam(':stateBilling', $data['stateBilling']);
                        $stmt->bindParam(':countryBilling', $data['countryBilling']);
                        $stmt->bindParam(':address1Shipping', $address1Shipping);
                        $stmt->bindParam(':address2Shipping', $address2Shipping);
                        $stmt->bindParam(':zipShipping', $zipShipping);
                        $stmt->bindParam(':cityShipping', $cityShipping);
                        $stmt->bindParam(':stateShipping', $stateShipping);
                        $stmt->bindParam(':countryShipping', $countryShipping);
                        $stmt->bindParam(':quantity', $data['quantity']);
                        $stmt->bindParam(':payment', $data['payment']);
                        $stmt->bindParam(':sameAsShipping', $sameAsShipping);
                        $stmt->execute();


//                    Create transport
                        $transporter = Swift_SmtpTransport::newInstance(EMAIL_HOST, EMAIL_PORT, 'ssl')
                            ->setUsername($myEmail)
                            ->setPassword(EMAIL_PASS);

//                    Create Twig Template
                        $template = $app['twig']->render('email.html.twig', array(
                                        'data' => $data,
                                        'totalAmount' => $data['quantity'] * $amount,
                                        'ecEmail'   => $ecEmail,
                                        'sameAsShipping' => $sameAsShipping,
                                    ));

//                    Create mailer
                        $mailer = Swift_Mailer::newInstance($transporter);
                        $message = Swift_Message::newInstance('Test')
                            ->setFrom(array($myEmail))
                            ->setTo($emailArray)
                            ->setSubject('I Browse Smart Purchase Order')
                            ->setBody($template, 'text/html');
                        $mailer->send($message);
                        $app['session']->getFlashBag()->add('success', 'Thank you for your purchase. Please come back again');

//                        $mailService = $app['service.mailservice'];
//                        $mailService->setup();
//                        $mailArray = array('myEmail' => $myEmail, 'toEmail' => $toEmail, 'template' => $template);
//                        $mailService->sendMail($mailArray);

                    }
                } catch(Error\Card $e){
                    $e_json = $e->getJsonBody();
                    $err = $e_json['error'];
                    $errors['stripe'] = $err['message'];
                }
            }
        } else {
            $form->addError(new FormError('This is a global error'));
            $app['session']->getFlashBag()->add('info', 'The form is bound, but not valid');
        }
    }

    return $app['twig']->render('purchase_gloves.html.twig', array('form' => $form->createView()));

})->bind('form');

$app->get('/page-with-cache', function () use ($app) {
    $response = new Response($app['twig']->render('page-with-cache.html.twig', array('date' => date('Y-M-d h:i:s'))));
    $response->setTtl(10);

    return $response;
})->bind('page_with_cache');

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
//            $app['monolog']->addDebug($e->getMessage());
    }

    return new Response($message, $code);
});

return $app;
