<?php

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

class FilterOperator implements OperatorInterface
{
    private $predicate;

    public function __construct(callable $predicate)
    {
        $this->predicate = $predicate;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $selectObserver = new CallbackObserver(
            function ($nextValue) use ($observer) {
                $shouldFire = false;
                try {
                    $shouldFire = call_user_func($this->predicate, $nextValue);
                } catch (\Exception $e) {
                    $observer->onError($e);
                }

                if ($shouldFire) {
                    $observer->onNext($nextValue);
                }
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        return $observable->subscribe($selectObserver);
    }
}