<?php

declare(strict_types=1);

namespace Fancourier\Objects;

class AwbTracker
{
    protected string $awbNumber = '';
    protected string $message = '';
    protected string $content = '';

    protected string $date = '';
    protected string $paymentDate = '';

    protected string $returnAwbNumber = '';
    protected string $redirectionAwbNumber = '';
    protected string $reimbursementAwbNumber = '';
    protected string $oPODAwbNumber = '';

    /** @var array<string, mixed> */
    protected array $confirmation = [];
    protected string $OTD = '';	// on time delivery - process total duration from pickup to delivery
    /** @var array<int, array<string, mixed>> */
    protected array $events = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        // awbNumber has no default in live payloads, but the typed property must
        // always be initialized (a `??` in the getter cannot protect it).
        $this->awbNumber = (string) ($data['awbNumber'] ?? '');
        $this->message = (string) ($data['message'] ?? '');
        $this->content = (string) ($data['content'] ?? '');

        if (!isset($data['message'])) {
            $this->date = (string) ($data['date'] ?? '');
            $this->paymentDate = (string) ($data['paymentDate'] ?? '');
            $this->returnAwbNumber = (string) ($data['returnAwbNumber'] ?? '');
            $this->redirectionAwbNumber = (string) ($data['redirectionAwbNumber'] ?? '');
            $this->reimbursementAwbNumber = (string) ($data['reimbursementAwbNumber'] ?? '');
            $this->oPODAwbNumber = (string) ($data['oPODAwbNumber'] ?? '');
        }

        $confirmation = $data['confirmation'] ?? null;
        $this->confirmation = is_array($confirmation) ? $confirmation : [];
        // OTD (on-time-delivery duration) may arrive as an int number of seconds.
        $this->OTD = (string) ($data['OTD'] ?? '');
        $events = $data['events'] ?? null;
        $this->events = is_array($events) ? $events : [];
    }

    public function getAwbNumber(): string
    {
        return $this->awbNumber;
    }

    public function getReturnAwbNumber(): string
    {
        return $this->returnAwbNumber;
    }

    public function getRedirectionAwbNumber(): string
    {
        return $this->redirectionAwbNumber;
    }

    public function getReimbursementAwbNumber(): string
    {
        return $this->reimbursementAwbNumber;
    }

    public function getOPODAwbNumber(): string
    {
        return $this->oPODAwbNumber;
    }

    public function getPaymentDate(): string
    {
        return $this->paymentDate;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function hasConfirmation(): bool
    {
        if (isset($this->confirmation['name']) && ($this->confirmation['name'] != '')) {
            return true;
        }

        return false;
    }

    /** @return array<string, mixed> */
    public function getConfirmation(): array
    {
        return $this->confirmation;
    }

    public function getOTD(): string
    {
        return $this->OTD;
    }

    /** @return array<int, array<string, mixed>> */
    public function getEvents(): array
    {
        return $this->events;
    }

    /** @return array<string, mixed> */
    public function getStatus(): array
    {
        if (count($this->events) > 0) {
            $last = array_key_last($this->events);

            return $this->events[$last];
        }

        return [
            'id' => null,
            'name' => $this->message,
            'location' => '',
            'date' => date("Y-m-d H:i:s"),
        ];
    }

}


/*
    [0] => Array
        (
            [content] =>
            [awbNumber] => 2000000000000
            [message] => The AWB has been registerd by sender
        )
    [0] => Array
        (
            [content] => Order #135
            [awbNumber] => 2000000000082
            [date] => 2023-11-28 00:00:00
            [paymentDate] =>
            [returnAwbNumber] =>
            [redirectionAwbNumber] =>
            [reimbursementAwbNumber] =>
            [oPODAwbNumber] =>
            [confirmation] => Array
                (
                    [name] =>
                    [date] => 2023-11-29
                )
            [OTD] => "24H"

            [events] => Array
                (
                    [0] => Array
                        (
                            [id] => C0
                            [name] => Expeditie ridicata
                            [location] => Bucuresti
                            [date] => 2023-11-28 18:58:00
                        )

                    [1] => Array
                        (
                            [id] => H3
                            [name] => Expeditie sortata pe banda
                            [location] => Bucuresti
                            [date] => 2023-11-28 21:57:06
                        )

                    [2] => Array
                        (
                            [id] => H3
                            [name] => Expeditie sortata pe banda
                            [location] => Bucuresti
                            [date] => 2023-11-29 00:00:44
                        )

                    [3] => Array
                        (
                            [id] => H10
                            [name] => Expeditie in tranzit spre depozitul de destinatie
                            [location] => Bucuresti
                            [date] => 2023-11-29 00:31:10
                        )

                    [4] => Array
                        (
                            [id] => H1
                            [name] => Expeditie descarcata in depozitulul de destinatie
                            [location] => Targu Frumos
                            [date] => 2023-11-29 08:56:21
                        )

                    [5] => Array
                        (
                            [id] => C1
                            [name] => Expeditie preluate spre livrare
                            [location] => Targu Frumos
                            [date] => 2023-11-29 09:17:00
                        )

                    [6] => Array
                        (
                            [id] => S1
                            [name] => Expeditie in livrare
                            [location] => Targu Frumos
                            [date] => 2023-11-29 09:17:00
                        )

                    [7] => Array
                        (
                            [id] => S12
                            [name] => Contactat; livrare ulterioara
                            [location] => Targu Frumos
                            [date] => 2023-11-29 09:50:13
                        )

                )

        )

*/
