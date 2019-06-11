<?php namespace Affiliates\Controllers;

use Affiliates\Models\Affiliate;
use Barryvdh\DomPDF\PDF as PDFObject;
use Carbon\Carbon;
use PDF;
use Flash;

class Coupons extends BaseController
{
    /**
     * @var array Extensions implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\ListController::class
    ];

    public $layout = 'default';

    public $pageTitle = 'Deine Coupons';

    public $publicActions = [];

    public $hiddenActions = [''];

    /**
     * @var array `ListController` configuration.
     */
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
    }

    public function listExtendQuery($query)
    {
        $query->where('affiliate_id', $this->user->id);
    }

    /**
     * @param Affiliate $affiliate
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getBillItems(Affiliate $affiliate, int $year, int $month)
    {
        $monthStart = Carbon::createFromDate($year, $month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $items = [];
        foreach ($affiliate->coupons as $coupon) {
            $orders = $coupon->orders()->whereHas('subscription')->whereBetween('created_at', [$monthStart, $monthEnd])->get();

            foreach($orders as $order) {
                $price = $coupon->is_direct_percent
                    ? intval($order->subscription_plan->direct_fee * (100 - $coupon->direct_amount) / 100)
                    : $order->subscription_plan->direct_fee - $coupon->direct_amount;

                $item = [
                    'order' => $order,
                    'name' => 'BBF Programm',
                    'date' => $order->created_at,
                    'qty' => 1,
                    'credit' => intval($price * 0.1)
                ];
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param Affiliate $affiliate
     * @param int $year
     * @param int $month
     * @return PDFObject
     */
    public function generateBillPDF(Affiliate $affiliate, int $year, int $month)
    {
        $items = $this->getBillItems($affiliate, $year, $month);

        if(count($items) === 0) {
            return null;
        }

        $total = 0;
        foreach($items as $item) {
            $total += $item['credit'];
        }
        $subtotal = intval(floor($total / 1.19));
        $tax = intval(ceil($subtotal * 0.19));

        $data = [
            'affiliate' => $affiliate,
            'code' => $affiliate->coupons->implode('code', ', '),
            'items' => $items,
            'total' => $total,
            'subtotal' => $subtotal,
            'tax' => $tax
        ];

        return PDF::loadView('bbf::pdf.affiliate_bill', $data);
    }

    public function bill(int $year, int $month)
    {
        if(Carbon::createFromDate($year, $month)->startOfMonth()->gte(Carbon::now()->startOfMonth())) {
            Flash::error('Abrechnungen können nur volle vergangene Monate erstellt werden');
            return redirect()->back();
        }

        $filename = 'Abrechnung_' . $this->user->username . '_' . $year . '_' . $month . '.pdf';


        if($pdf = $this->generateBillPDF($this->user, $year, $month)) {
            return $pdf->download($filename);
        }

        Flash::error('Du hast für den gewählten Monat leider keine Umsätze');
        return redirect()->back();
    }
}