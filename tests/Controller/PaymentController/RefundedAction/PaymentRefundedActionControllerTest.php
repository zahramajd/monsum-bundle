<?php

namespace Speicher210\MonsumBundle\Test\Controller\PaymentController\RefundedAction;

use Speicher210\Monsum\Api\Model\Notification\Customer\Customer;
use Speicher210\Monsum\Api\Model\Notification\Payment\Payment;
use Speicher210\Monsum\Api\Model\Notification\Payment\PaymentRefundedNotification;
use Speicher210\Monsum\Api\Model\Notification\Payment\PaymentSubscription;
use Speicher210\MonsumBundle\Event\PaymentRefundedEvent;
use Speicher210\MonsumBundle\MonsumNotificationEvents;
use Speicher210\MonsumBundle\Test\Controller\AbstractControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test case for payment refunded notification.
 */
class PaymentRefundedActionControllerTest extends AbstractControllerTestCase
{
    public function testRefundedActionReturns204AndRaisesEventIfRequestIsValid()
    {
        $client = parent::createClient();

        $mock = $this->getMock('stdClass', array('eventHandlerCallback'));
        $mock->expects(static::once())->method('eventHandlerCallback')->with(
            static::callback(function (PaymentRefundedEvent $event) {
                $payloadData = $event->getPayloadData();
                static::assertNotNull($payloadData);

                $expectedPayloadData = new PaymentRefundedNotification();
                $expectedPayloadData
                    ->setId(398160)
                    ->setType('payment.refunded')
                    ->setCreated(new \DateTime('2014-03-13T10:37:08+0000'))
                    ->setCustomer(
                        (new Customer())
                            ->setCustomerId(578266)
                            ->setCustomerExternalId('id_007')
                            ->setCustomerNumber('cn_77')
                            ->setHash('hash123')
                            ->setCompanyName('Muster GmbH')
                            ->setTitle('Dr.')
                            ->setSalutation('Herr')
                            ->setFirstName('Martin')
                            ->setLastName('Muster')
                            ->setAddress('Am Musterhuegel 12')
                            ->setAddress2('Postfach 4')
                            ->setZipCode('01234')
                            ->setCity('Musterhausen')
                            ->setCountryCode('DE')
                            ->setEmail('m.muster@email.de')
                            ->setPaymentDataUrl('https://app.monsum.com/accountdata/a1/payment_data_url')
                            ->setDashboardUrl('https://app.monsum.com/dashboard/a1/dashboard_url')
                    )
                    ->setSubscription(
                        (new PaymentSubscription())
                            ->setSubscriptionId(216084)
                            ->setHash('hash123')
                            ->setArticleCode(72)
                            ->setQuantity(1)
                    )
                    ->setPayment(
                        (new Payment())
                            ->setPaymentId(180828)
                            ->setInvoiceId('447236')
                            ->setInvoiceNumber('8')
                            ->setInvoiceUrl('https://app.monsum.com/download/invoice_url')
                            ->setTotalAmount('4.7005')
                            ->setCurrency('EUR')
                            ->setMethod('creditcard')
                            ->setGateway('Adyen')
                            ->setTest('1')
                            ->setType('charge')
                            ->setStatus('open')
                            ->setNextEvent(new \DateTime('2014-03-27T00:00:00+0000'))
                            ->setCreated(new \DateTime('2014-03-06T00:00:00+0000'))
                    );

                static::assertEquals($expectedPayloadData, $payloadData);

                return true;
            })
        );
        $client
            ->getContainer()
            ->get('event_dispatcher')
            ->addListener(MonsumNotificationEvents::INCOMING_PAYMENT_REFUNDED, array($mock, 'eventHandlerCallback'));

        $data = file_get_contents(__DIR__ . '/Fixtures/' . $this->getName() . '.json');
        $client = $this->makeRequest('speicher210_monsum_notification_payment_refunded', $data, $client);

        static::assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    }
}
