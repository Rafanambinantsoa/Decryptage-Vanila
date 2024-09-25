<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use phpseclib\Crypt\TripleDES;
use DateTime;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class PaymentController extends Controller
{
    //Pour le message de success
    public function success(Request $request)
    {

        Mail::raw('Test email', function ($message) {
            $message->to('akutagawakarim@gmail.com')
                ->subject('Test Email Subject');
        });

        return response(200);
    }

    //Pour le demarage du paiment vanila pay 
    public function initPayment(Request $request)
    {
        $validator = FacadesValidator::make($request->all(), [
            "nom" => 'required',
            "total" => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                "Message" => "There is an empty things dude "
            ]);
        }

        // Exemple de données à envoyer
        $total = $request->total; // Montant à payer
        $nom = $request->nom; // Nom du payeur
        $mail = 'mail@mail.com'; // Adresse email du payeur
        $site_url = 'https://karimrafanambinantsoa.vercel.app'; // URL du site e-commerce
        $ip = $request->ip(); // Adresse IP du client (Laravel peut détecter l'IP)
        $now = new DateTime(); // Date du paiement
        $daty = $now->format('Y-m-d'); // Formattage de date

        // Clés de sécurité
        $public_key = '4c36cff9f00736b959c123ffcabf853aeae44d956b66f35f43'; // Clé publique obtenue de la plateforme AriaryNet
        $private_key = 'f15df968cc90b8dc117a3c041e403ee96659333c34a3978339'; // Clé privée obtenue de la plateforme AriaryNet

        // Authentification pour obtenir le token
        $auth_params = [
            'client_id' => '399_4umss3k06x6oc8gcg04gcw4wokkokw080wssg40k040oksk8c4',
            'client_secret' => '2fbaa5olmlxc8o4ssco40oosk840840k40ckk0008k8wggc80w',
            'grant_type' => 'client_credentials'
        ];

        $curl = curl_init();
        $url = 'https://pro.ariarynet.com/oauth/v2/token';

        curl_setopt($curl, CURLOPT_HTTPHEADER, []);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $auth_params);
        curl_setopt($curl, CURLOPT_URL, $url);
        $result = curl_exec($curl);
        curl_close($curl);

        // Obtenir le token
        $json = json_decode($result);
        // dd($json);
        $token = $json->access_token;
        // dd($token);

        // Headers pour les requêtes API
        $headers = ["Authorization:Bearer " . $token];

        // Cryptage TripleDES avec mode CBC et IV
        $des = new TripleDES();
        $des->setKey($public_key);
        $des->setIV(str_repeat("\0", 8)); // IV de 8 octets pour TripleDES
        // $des->setMode(TripleDES::MODE_CBC); // Mode CBC

        // Données à envoyer pour la transaction
        $params_to_send = array(
            "unitemonetaire" => "Ar",
            "adresseip"      => $request->ip(), // Utilisation de l'IP réelle du client
            "date"           => $daty,
            "idpanier"       => uniqid(), // ID de panier unique généré
            "montant"        => $total,
            "nom"            => $nom,
            "reference"      => 'fdfd',
            "site_url" => $site_url // Référence interne optionnelle
        );

        // Chiffrement des paramètres
        $params_crypt = $des->encrypt(json_encode($params_to_send));
        // Paramètres envoyés à l'API
        $kim = array(
            "params"   => $params_crypt,
            "site_url" => $site_url
        );
        // $params['site_url'] = $site_url;

        // Appel de l'API pour obtenir l'ID de paiement
        $curl = curl_init();
        $url = 'https://pro.ariarynet.com/api/paiements';

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params_to_send); // Envoyer les paramètres en tant que JSON
        curl_setopt($curl, CURLOPT_URL, $url);
        $result = curl_exec($curl);

        if ($result === false) {
            dd('Erreur cURL : ' . curl_error($curl));
        }
        curl_close($curl);

        // dd("Résultat cURL : " . $result);

        // Décryptage de l'ID de paiement (vérifiez d'abord si $result est correct)
        $des->setKey($private_key);
        $id = $des->decrypt($result);
        dd($id);
        return response($id);
    }
}
