<?php

namespace App\Services;

use App\Models\QrCode;
use App\Models\Dossier;
use App\Models\Organisation;
use Illuminate\Support\Str;

class QrCodeService
{
    /**
     * Générer un QR Code pour un dossier
     */
    public function generateForDossier(Dossier $dossier)
    {
        try {
            // Vérifier que le dossier existe et a une organisation
            if (!$dossier || !$dossier->organisation) {
                \Log::error('QrCodeService: Dossier ou organisation manquant', [
                    'dossier_id' => $dossier->id ?? 'null',
                    'organisation_present' => $dossier->organisation ? 'yes' : 'no'
                ]);
                return null;
            }

            $organisation = $dossier->organisation;
            
            // Générer un code unique
            $code = 'QR-' . strtoupper(Str::random(16));
            
            // Préparer les données de vérification - STRUCTURE CORRIGÉE
            $donneesVerification = [
                'dossier_numero' => $dossier->numero_dossier,
                'organisation_nom' => $organisation->nom,
                'organisation_type' => $organisation->type,
                'numero_recepisse' => $organisation->numero_recepisse,
                'date_soumission' => $dossier->submitted_at ? $dossier->submitted_at->toISOString() : now()->toISOString(),
                'statut' => $dossier->statut,
                'province' => $organisation->province,
                'hash_verification' => null // Sera calculé après
            ];

            // Calculer le hash de vérification
            $hashVerification = hash('sha256', json_encode($donneesVerification, JSON_UNESCAPED_UNICODE));
            $donneesVerification['hash_verification'] = $hashVerification;

            // Créer l'enregistrement QR Code - CORRECTION LIGNE 44 PROBABLE
            $qrCode = QrCode::create([
                'code' => $code,
                'type' => 'dossier_verification',
                'verifiable_type' => Dossier::class,
                'verifiable_id' => $dossier->id,
                'document_numero' => $dossier->numero_dossier,
                'donnees_verification' => json_encode($donneesVerification, JSON_UNESCAPED_UNICODE),
                'hash_verification' => $hashVerification,
                'nombre_verifications' => 0,
                'expire_at' => now()->addYears(5), // QR Code valide 5 ans
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            \Log::info('QR Code généré avec succès', [
                'qr_code_id' => $qrCode->id,
                'code' => $code,
                'dossier_id' => $dossier->id,
                'organisation_id' => $organisation->id
            ]);

            return $qrCode;

        } catch (\Exception $e) {
            \Log::error('Erreur génération QR Code', [
                'dossier_id' => $dossier->id ?? 'null',
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            // Retourner null en cas d'erreur pour permettre la continuation du processus
            return null;
        }
    }

    /**
     * Vérifier un QR Code
     */
    public function verifyQrCode($code)
    {
        try {
            $qrCode = QrCode::where('code', $code)
                ->where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('expire_at')
                          ->orWhere('expire_at', '>', now());
                })
                ->first();

            if (!$qrCode) {
                return [
                    'success' => false,
                    'message' => 'QR Code non trouvé ou expiré'
                ];
            }

            // Incrementer le compteur de vérifications
            $qrCode->increment('nombre_verifications');
            $qrCode->update(['derniere_verification' => now()]);

            // Décoder les données de vérification
            $donneesVerification = [];
            if ($qrCode->donnees_verification) {
                if (is_string($qrCode->donnees_verification)) {
                    $donneesVerification = json_decode($qrCode->donnees_verification, true) ?? [];
                } elseif (is_array($qrCode->donnees_verification)) {
                    $donneesVerification = $qrCode->donnees_verification;
                }
            }

            return [
                'success' => true,
                'qr_code' => $qrCode,
                'donnees' => $donneesVerification,
                'verifications_count' => $qrCode->nombre_verifications
            ];

        } catch (\Exception $e) {
            \Log::error('Erreur vérification QR Code', [
                'code' => $code,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification'
            ];
        }
    }

    /**
     * Générer un QR Code pour une organisation
     */
    public function generateForOrganisation(Organisation $organisation)
    {
        try {
            $code = 'ORG-' . strtoupper(Str::random(16));
            
            $donneesVerification = [
                'organisation_nom' => $organisation->nom,
                'organisation_type' => $organisation->type,
                'numero_recepisse' => $organisation->numero_recepisse,
                'statut' => $organisation->statut,
                'province' => $organisation->province,
                'date_creation' => $organisation->date_creation->toISOString(),
                'hash_verification' => null
            ];

            $hashVerification = hash('sha256', json_encode($donneesVerification, JSON_UNESCAPED_UNICODE));
            $donneesVerification['hash_verification'] = $hashVerification;

            $qrCode = QrCode::create([
                'code' => $code,
                'type' => 'organisation_verification',
                'verifiable_type' => Organisation::class,
                'verifiable_id' => $organisation->id,
                'document_numero' => $organisation->numero_recepisse,
                'donnees_verification' => json_encode($donneesVerification, JSON_UNESCAPED_UNICODE),
                'hash_verification' => $hashVerification,
                'nombre_verifications' => 0,
                'expire_at' => null, // Pas d'expiration pour les organisations
                'is_active' => true
            ]);

            return $qrCode;

        } catch (\Exception $e) {
            \Log::error('Erreur génération QR Code organisation', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Générer l'URL de vérification publique
     */
    public function getVerificationUrl($qrCode)
    {
        if (is_string($qrCode)) {
            $code = $qrCode;
        } elseif (is_object($qrCode) && isset($qrCode->code)) {
            $code = $qrCode->code;
        } else {
            return null;
        }

        return url('/verify-qr/' . $code);
    }

    /**
     * Invalider un QR Code
     */
    public function invalidateQrCode($code)
    {
        try {
            $qrCode = QrCode::where('code', $code)->first();
            
            if ($qrCode) {
                $qrCode->update(['is_active' => false]);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            \Log::error('Erreur invalidation QR Code', [
                'code' => $code,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}