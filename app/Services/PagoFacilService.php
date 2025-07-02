<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class PagoFacilService
{
    // Credenciales de PagoFácil
    private $tokenService = '51247fae280c20410824977b0781453df59fad5b23bf2a0d14e884482f91e09078dbe5966e0b970ba696ec4caf9aa5661802935f86717c481f1670e63f35d5041c31d7cc6124be82afedc4fe926b806755efe678917468e31593a5f427c79cdf016b686fca0cb58eb145cf524f62088b57c6987b3bb3f30c2082b640d7c52907';
    private $tokenSecret = '9E7BC239DDC04F83B49FFDA5';
    public $tcCommerceID = "d029fa3a95e174a19934857f535eb9427d967218a36ea014b70ad704bc6c8d1c";

    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Obtiene el token de autenticación de PagoFácil.
     * @return string El token de acceso.
     * @throws \Exception Si ocurre un error al obtener el token.
     */
    public function obtenerToken()
    {
        try {
            $url = "https://serviciostigomoney.pagofacil.com.bo/api/servicio/login";
            $body = [
                'TokenService' => $this->tokenService,
                'TokenSecret' => $this->tokenSecret
            ];
            $headers = [
                'Accept' => 'application/json'
            ];

            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $body
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (!isset($result["values"])) {
                Log::error('PagoFacilService: No se pudo obtener un token válido.', ['response' => $result]);
                throw new \Exception("PagoFacilService: No se pudo obtener un token válido para el pago.");
            }

            return $result["values"];
        } catch (\Throwable $th) {
            Log::error('PagoFacilService: Error al obtener el token: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);
            throw new \Exception("PagoFacilService: Error al obtener el token: " . $th->getMessage());
        }
    }

    /**
     * Realiza una solicitud de pago QR a PagoFácil.
     * @param array $data Los datos de la transacción para PagoFácil.
     * @return array La respuesta de la API de PagoFácil (QR, Nro. Transacción).
     * @throws \Exception Si ocurre un error en la solicitud de pago QR.
     */
    public function generarPagoQR(array $data)
    {
        try {
            $accessToken = $this->obtenerToken();
            $urlPagoQR = "https://serviciostigomoney.pagofacil.com.bo/api/servicio/pagoqr";

            $headers = [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ];

            $response = $this->client->post($urlPagoQR, [
                'headers' => $headers,
                'json' => $data
            ]);

            $responseContent = $response->getBody()->getContents();
            $result = json_decode($responseContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('PagoFacilService: Error al decodificar JSON de pagoQR', [
                    'error_message' => json_last_error_msg(),
                    'response_content' => $responseContent
                ]);
                throw new \Exception("PagoFacilService: Error al procesar la respuesta del servicio de pago.");
            }

            if (!isset($result['values'])) {
                Log::error('PagoFacilService: El campo "values" no está presente en la respuesta de PagoFácil', ['result' => $result]);
                throw new \Exception("PagoFacilService: Respuesta inesperada del servicio de pago.");
            }

            $valuesParts = explode(";", $result['values']);

            if (count($valuesParts) < 2) {
                Log::error('PagoFacilService: El campo "values" no contiene el formato esperado', ['values' => $result['values']]);
                throw new \Exception("PagoFacilService: Formato de respuesta inesperado del servicio de pago.");
            }

            $nroTransaccion = $valuesParts[0];
            $jsonEscaped = $valuesParts[1];
            $qrData = json_decode($jsonEscaped, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($qrData['qrImage'])) {
                Log::error('PagoFacilService: Error al decodificar JSON del QR o imagen no encontrada', [
                    'error_message' => json_last_error_msg(),
                    'json_escaped' => $jsonEscaped,
                    'qr_data' => $qrData
                ]);
                throw new \Exception("PagoFacilService: No se pudo obtener la imagen del código QR.");
            }

            return [
                'qrImageBase64' => "data:image/png;base64," . $qrData['qrImage'],
                'nroTransaccionPagoFacil' => $nroTransaccion
            ];

        } catch (\Throwable $th) {
            Log::error('PagoFacilService: Error general al generar pago QR: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);
            throw new \Exception("PagoFacilService: Error al procesar el pago QR: " . $th->getMessage());
        }
    }
}