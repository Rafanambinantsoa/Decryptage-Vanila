<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use phpseclib\Crypt\TripleDES;
use DateTime;
use GuzzleHttp\Client;

class DecryptController extends Controller
{
    private $publicKey = '3d66483a6e2859630c2a66cf3327deb6d953668133ea158c44'; // À obtenir depuis la plateforme pro.ariarynet.com
    private $privateKey = 'f2766d40457bde555a1c221261c753be795f9b9db9e48b0c91'; // À obtenir depuis la plateforme pro.ariarynet.com
    private $clientId = '399_4umss3k06x6oc8gcg04gcw4wokkokw080wssg40k040oksk8c4'; // À obtenir depuis la plateforme pro.ariarynet.com
    private $clientSecret = '2fbaa5olmlxc8o4ssco40oosk840840k40ckk0008k8wggc80w'; // À obtenir depuis la plateforme pro.ariarynet.com

    public function initiatePayment(Request $request)
    {
        // Données à envoyer
        $total = 1000; // Montant à payer
        $nom = 'Mikasa Tsukasa'; // Nom du payeur
        $email = 'akutagawakarim@gmail.com'; // Email du payeur
        $siteUrl = 'https://vanilla.unityfianar.site'; // URL du site e-commerce
        $ip = '89.116.111.200'; // Adresse IP du site e-commerce
        $now = new DateTime(); // Date du paiement
        $daty = $now->format('Y-m-d'); // Format de la date

        // Ajuster les clés à une longueur supportée (16 ou 24 octets)
        $publicKey = substr($this->publicKey, 0, 24); // Tronquer ou adapter la longueur
        $privateKey = substr($this->privateKey, 0, 24);

        // Étape 1 : Obtenir le token d'authentification
        $token = $this->getAccessToken();

        if (!$token) {
            return response()->json(['error' => 'Erreur lors de l\'obtention du token'], 500);
        }

        // Étape 2 : Préparation des données à chiffrer
        $paramsToSend = [
            "unitemonetaire" => "Ar",
            "adresseip"      => $ip,
            "date"           => $daty,
            "idpanier"       => $ip, // Tu peux changer cet ID
            "montant"        => $total,
            "nom"            => $nom,
            "reference"      => '' // Référence interne pour ton panier
        ];

        // Chiffrement des données avec TripleDES
        $cipher = new TripleDES();
        // $cipher = new TripleDES('cbc');
        $cipher->setKey($publicKey);
        // $cipher->setIV("\x00\x00\x00\x00\x00\x00\x00\x00");
        $encryptedParams = $cipher->encrypt(json_encode($paramsToSend));
        // $encryptedParamsBase64 = base64_encode($encryptedParams);

        // Étape 3 : Initialisation du paiement
        $paymentParams = array(
            'site_url' => $siteUrl,
            'params'   => $encryptedParams
        );

        $paymentId = $this->initiatePaymentRequest($token, $paymentParams);

        if (!$paymentId) {
            return response()->json(['error' => 'Erreur lors de l\'initialisation du paiement'], 500);
        }

        // Déchiffrement de l'ID du paiement
        $cipher->setKey($privateKey);
        $decryptedId = $cipher->decrypt($paymentId);

        // Redirection vers la page de paiement
        return response()->json([
            'payment_url' => "https://moncompte.ariarynet.com/payer/{$decryptedId}"
        ]);
    }

    private function getAccessToken()
    {
        // Authentification pour obtenir le token d'accès
        $client = new Client();
        $response = $client->post('https://pro.ariarynet.com/oauth/v2/token', [
            'form_params' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials'
            ]
        ]);

        if ($response->getStatusCode() == 200) {
            $body = json_decode($response->getBody(), true);
            return $body['access_token'] ?? null;
        }

        return null;
    }

    private function initiatePaymentRequest($token, $params)
    {
        // Envoie de la requête d'initialisation de paiement
        $client = new Client();
        $response = $client->post('https://pro.ariarynet.com/api/paiements', [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
            'form_params' => $params
        ]);

        if ($response->getStatusCode() == 200) {
            return $response->getBody()->getContents();
        }

        return null;
    }
}
