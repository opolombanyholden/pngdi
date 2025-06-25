<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\Document;
use App\Models\Adherent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class QrCodeService
{
    /**
     * Générer un QR Code pour un document
     */
    public function generateForDocument(Document $document): array
    {
        // Générer un code de vérification unique
        $verificationCode = $this->generateVerificationCode('doc', $document->id);
        
        // Créer l'URL de vérification
        $verificationUrl = $this->generateVerificationUrl('document', $verificationCode);
        
        // Générer le QR Code (utilisant une bibliothèque simple)
        $qrCodeData = $this->generateQrCodeImage($verificationUrl);
        
        // Sauvegarder le QR Code
        $filename = 'qrcodes/documents/' . $document->id . '.png';
        Storage::put($filename, $qrCodeData);
        
        // Mettre à jour le document avec le code de vérification
        $document->update([
            'metadata' => array_merge($document->metadata ?? [], [
                'verification_code' => $verificationCode,
                'qrcode_path' => $filename,
                'qrcode_url' => Storage::url($filename)
            ])
        ]);
        
        return [
            'verification_code' => $verificationCode,
            'verification_url' => $verificationUrl,
            'qrcode_path' => $filename,
            'qrcode_data' => base64_encode($qrCodeData)
        ];
    }
    
    /**
     * Générer un QR Code pour une organisation (récépissé)
     */
    public function generateForOrganisation(Organisation $organisation): array
    {
        // Générer un code de vérification unique
        $verificationCode = $this->generateVerificationCode('org', $organisation->id);
        
        // Créer l'URL de vérification
        $verificationUrl = $this->generateVerificationUrl('organisation', $verificationCode);
        
        // Données à encoder dans le QR Code
        $qrData = json_encode([
            'url' => $verificationUrl,
            'org' => $organisation->sigle ?? substr($organisation->nom, 0, 10),
            'num' => $organisation->numero_recepisse,
            'type' => $organisation->type
        ]);
        
        // Générer le QR Code
        $qrCodeImage = $this->generateQrCodeImage($qrData);
        
        // Sauvegarder le QR Code
        $filename = 'qrcodes/organisations/' . $organisation->id . '.png';
        Storage::put($filename, $qrCodeImage);
        
        // Mettre à jour l'organisation
        $organisation->update([
            'metadata' => array_merge($organisation->metadata ?? [], [
                'verification_code' => $verificationCode,
                'qrcode_path' => $filename,
                'qrcode_url' => Storage::url($filename)
            ])
        ]);
        
        return [
            'verification_code' => $verificationCode,
            'verification_url' => $verificationUrl,
            'qrcode_path' => $filename,
            'qrcode_data' => base64_encode($qrCodeImage)
        ];
    }
    
    /**
     * Générer un QR Code pour une carte d'adhérent
     */
    public function generateForAdherent(Adherent $adherent): array
    {
        // Générer un code de vérification unique
        $verificationCode = $this->generateVerificationCode('adh', $adherent->id);
        
        // Créer l'URL de vérification
        $verificationUrl = $this->generateVerificationUrl('adherent', $verificationCode);
        
        // Données minimales pour le QR Code
        $qrData = json_encode([
            'url' => $verificationUrl,
            'num' => $adherent->numero_carte,
            'nip' => substr($adherent->nip, -4) // Derniers 4 chiffres seulement pour la sécurité
        ]);
        
        // Générer le QR Code
        $qrCodeImage = $this->generateQrCodeImage($qrData);
        
        // Sauvegarder le QR Code
        $filename = 'qrcodes/adherents/' . $adherent->id . '.png';
        Storage::put($filename, $qrCodeImage);
        
        // Mettre à jour l'adhérent
        $adherent->update([
            'metadata' => array_merge($adherent->metadata ?? [], [
                'verification_code' => $verificationCode,
                'qrcode_path' => $filename,
                'qrcode_url' => Storage::url($filename)
            ])
        ]);
        
        return [
            'verification_code' => $verificationCode,
            'verification_url' => $verificationUrl,
            'qrcode_path' => $filename,
            'qrcode_data' => base64_encode($qrCodeImage)
        ];
    }
    
    /**
     * Générer un lien sécurisé pour l'auto-enregistrement des adhérents
     */
    public function generateSecureRegistrationLink(Organisation $organisation): array
    {
        // Générer un token unique
        $token = Str::random(32);
        $expiresAt = now()->addDays(30); // Valide pendant 30 jours
        
        // Créer l'URL d'enregistrement
        $registrationUrl = URL::temporarySignedRoute(
            'public.adherent.register',
            $expiresAt,
            [
                'organisation' => $organisation->id,
                'token' => $token
            ]
        );
        
        // Générer le QR Code pour le lien
        $qrCodeImage = $this->generateQrCodeImage($registrationUrl);
        
        // Sauvegarder les informations
        $organisation->update([
            'metadata' => array_merge($organisation->metadata ?? [], [
                'registration_token' => $token,
                'registration_token_expires_at' => $expiresAt->toDateTimeString(),
                'registration_url' => $registrationUrl
            ])
        ]);
        
        return [
            'token' => $token,
            'url' => $registrationUrl,
            'expires_at' => $expiresAt,
            'qrcode_data' => base64_encode($qrCodeImage),
            'short_url' => $this->generateShortUrl($registrationUrl)
        ];
    }
    
    /**
     * Vérifier un code de vérification
     */
    public function verifyCode(string $type, string $code): array
    {
        $parts = explode('-', $code);
        
        if (count($parts) !== 3) {
            return [
                'valid' => false,
                'message' => 'Format de code invalide'
            ];
        }
        
        list($prefix, $id, $hash) = $parts;
        
        switch ($type) {
            case 'document':
                $model = Document::find($id);
                break;
            case 'organisation':
                $model = Organisation::find($id);
                break;
            case 'adherent':
                $model = Adherent::find($id);
                break;
            default:
                return [
                    'valid' => false,
                    'message' => 'Type de vérification invalide'
                ];
        }
        
        if (!$model) {
            return [
                'valid' => false,
                'message' => 'Document non trouvé'
            ];
        }
        
        // Vérifier le hash
        $expectedHash = $this->generateHash($prefix, $id);
        
        if ($hash !== $expectedHash) {
            return [
                'valid' => false,
                'message' => 'Code de vérification invalide'
            ];
        }
        
        // Retourner les informations
        return [
            'valid' => true,
            'type' => $type,
            'data' => $this->getVerificationData($type, $model)
        ];
    }
    
    /**
     * Générer un code de vérification unique
     */
    protected function generateVerificationCode(string $prefix, int $id): string
    {
        $hash = $this->generateHash($prefix, $id);
        return sprintf('%s-%d-%s', $prefix, $id, $hash);
    }
    
    /**
     * Générer un hash sécurisé
     */
    protected function generateHash(string $prefix, int $id): string
    {
        $secret = config('app.key');
        return substr(hash_hmac('sha256', $prefix . $id, $secret), 0, 8);
    }
    
    /**
     * Générer l'URL de vérification
     */
    protected function generateVerificationUrl(string $type, string $code): string
    {
        return route('public.verify', [
            'type' => $type,
            'code' => $code
        ]);
    }
    
    /**
     * Générer une URL courte
     */
    protected function generateShortUrl(string $url): string
    {
        // Pour l'instant, retourner l'URL normale
        // TODO: Implémenter un service de raccourcissement d'URL
        return $url;
    }
    
    /**
     * Obtenir les données de vérification selon le type
     */
    protected function getVerificationData(string $type, $model): array
    {
        switch ($type) {
            case 'document':
                return [
                    'type_document' => $model->documentType->nom,
                    'organisation' => $model->dossier->organisation->nom,
                    'date_creation' => $model->created_at->format('d/m/Y'),
                    'statut' => $model->status_label,
                    'validé_par' => $model->validatedBy ? $model->validatedBy->name : null,
                    'validé_le' => $model->validated_at ? $model->validated_at->format('d/m/Y H:i') : null
                ];
                
            case 'organisation':
                return [
                    'nom' => $model->nom,
                    'sigle' => $model->sigle,
                    'type' => $model->type_label,
                    'numero_recepisse' => $model->numero_recepisse,
                    'date_creation' => $model->date_creation ? $model->date_creation->format('d/m/Y') : null,
                    'statut' => $model->statut_label,
                    'adresse' => $model->adresse_complete,
                    'nombre_adherents' => $model->adherentsActifs()->count(),
                    'nombre_etablissements' => $model->etablissements()->count()
                ];
                
            case 'adherent':
                return [
                    'nom_complet' => $model->nom_complet,
                    'numero_carte' => $model->numero_carte,
                    'organisation' => $model->organisation->nom,
                    'date_adhesion' => $model->date_adhesion ? $model->date_adhesion->format('d/m/Y') : null,
                    'statut' => $model->is_active ? 'Actif' : 'Inactif',
                    'is_fondateur' => $model->is_fondateur
                ];
                
            default:
                return [];
        }
    }
    
    /**
     * Générer une image QR Code simple (placeholder)
     * Note: Dans un environnement de production, utiliser une vraie bibliothèque QR Code
     */
    protected function generateQrCodeImage(string $data): string
    {
        // Pour l'instant, créer une image placeholder
        // TODO: Implémenter avec une vraie bibliothèque QR Code
        
        $width = 200;
        $height = 200;
        
        // Créer une image
        $image = imagecreatetruecolor($width, $height);
        
        // Couleurs
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        // Fond blanc
        imagefilledrectangle($image, 0, 0, $width, $height, $white);
        
        // Texte placeholder
        $text = "QR Code\n" . substr(md5($data), 0, 8);
        $font = 3;
        $lines = explode("\n", $text);
        $y = 80;
        
        foreach ($lines as $line) {
            $textWidth = imagefontwidth($font) * strlen($line);
            $x = ($width - $textWidth) / 2;
            imagestring($image, $font, $x, $y, $line, $black);
            $y += 20;
        }
        
        // Bordure
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $black);
        
        // Capturer l'image
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        
        // Libérer la mémoire
        imagedestroy($image);
        
        return $imageData;
    }
    
    /**
     * Nettoyer les anciens QR Codes
     */
    public function cleanupOldQrCodes(int $daysOld = 90): int
    {
        $count = 0;
        $cutoffDate = now()->subDays($daysOld);
        
        // Nettoyer les QR Codes des documents
        $oldDocuments = Document::where('created_at', '<', $cutoffDate)
            ->whereJsonContains('metadata->qrcode_path', 'qrcodes/')
            ->get();
        
        foreach ($oldDocuments as $document) {
            if (isset($document->metadata['qrcode_path']) && 
                Storage::exists($document->metadata['qrcode_path'])) {
                Storage::delete($document->metadata['qrcode_path']);
                $count++;
            }
        }
        
        return $count;
    }
}