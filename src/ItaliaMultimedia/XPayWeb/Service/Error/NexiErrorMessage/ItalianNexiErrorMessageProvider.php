<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service\Error\NexiErrorMessage;

use ItaliaMultimedia\XPayWeb\Enum\NexiErrorCode;

final class ItalianNexiErrorMessageProvider
{
    public function getCustomerMessage(?NexiErrorCode $errorCode): string
    {
        return match ($errorCode) {
            NexiErrorCode::DUPLICATE_ORDER => 'Questa richiesta di pagamento esiste già. Aggiorna la pagina e riprova.',
            NexiErrorCode::ORDER_NOT_FOUND,
            NexiErrorCode::ORDER_OPERATION_NOT_FOUND,
            NexiErrorCode::TRANSACTION_NOT_FOUND => 'Il pagamento non è stato trovato. Contatta l’assistenza.',
            NexiErrorCode::SERVICE_TEMPORARILY_UNAVAILABLE => 'Il servizio di pagamento è temporaneamente '
                . 'non disponibile. Riprova più tardi.',
            NexiErrorCode::GENERIC_API_ERROR,
            NexiErrorCode::INTERNAL_ERROR,
            NexiErrorCode::PAYMENT_INTERNAL_ERROR,
            NexiErrorCode::PAYMENT_SETUP_INTERNAL_ERROR,
            NexiErrorCode::PAYMENT_VALIDATION_INTERNAL_ERROR => 'Il servizio di pagamento ha avuto un problema '
                . 'temporaneo. Riprova.',
            default => 'Il pagamento non è stato completato. Riprova o contatta l’assistenza.',
        };
    }
}
