<?php

namespace CoinsPay\Exception;

/**
 * The API answered with an error: HTTP status 4xx or 5xx and a body
 * {"error": {"code", "message", "details"}}. Branch on getErrorCode(): the
 * message is an English explanation and may change.
 */
class ApiException extends CoinsPayException
{
    /** @var int */
    private $status;

    /** @var string */
    private $errorCode;

    /** @var array<string, mixed>|null */
    private $details;

    /** @var array<string, string> */
    private $headers;

    /**
     * @param int $status
     * @param string $errorCode
     * @param string $message
     * @param array<string, mixed>|null $details
     * @param array<string, string> $headers Response headers, lowercase names.
     */
    public function __construct($status, $errorCode, $message, $details = null, array $headers = [])
    {
        parent::__construct($message);
        $this->status = (int) $status;
        $this->errorCode = (string) $errorCode;
        $this->details = is_array($details) ? $details : null;
        $this->headers = $headers;
    }

    /**
     * The subclass that matches the HTTP status.
     *
     * @param int $status
     * @param string $errorCode
     * @param string $message
     * @param array<string, mixed>|null $details
     * @param array<string, string> $headers
     * @return ApiException
     */
    public static function create($status, $errorCode, $message, $details = null, array $headers = [])
    {
        switch ((int) $status) {
            case 400:
                return new InvalidRequestException($status, $errorCode, $message, $details, $headers);
            case 401:
                return new AuthenticationException($status, $errorCode, $message, $details, $headers);
            case 403:
                return new PermissionDeniedException($status, $errorCode, $message, $details, $headers);
            case 404:
                return new NotFoundException($status, $errorCode, $message, $details, $headers);
            case 409:
                return new ConflictException($status, $errorCode, $message, $details, $headers);
            case 422:
                return new UnprocessableException($status, $errorCode, $message, $details, $headers);
            case 429:
                return new RateLimitException($status, $errorCode, $message, $details, $headers);
            case 503:
                return new ServiceUnavailableException($status, $errorCode, $message, $details, $headers);
            default:
                return new ApiException($status, $errorCode, $message, $details, $headers);
        }
    }

    /** @return int HTTP status. */
    public function getStatus()
    {
        return $this->status;
    }

    /** @return string Error code such as KEY_ACCESS_DENIED; see CoinsPay\ErrorCode. */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /** @return array<string, mixed>|null For VALIDATION_ERROR: field path => reason. */
    public function getDetails()
    {
        return $this->details;
    }

    /** @return array<string, string> Response headers, lowercase names. */
    public function getHeaders()
    {
        return $this->headers;
    }
}
