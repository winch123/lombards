<?php

namespace PolombardamModels;

class ErrorReport extends ModelBase {

    /**
     *
     * @var int
     */
    public $id;

    /**
     *
     * @var int
     */
    public $merchant_id;

    /**
     *
     * @var string
     */
    public $merchant_name;

    /**
     *
     * @var boolean
     */
    public $merchant_close;

    /**
     *
     * @var boolean
     */
    public $merchant_bad_adress;

    /**
     *
     * @var boolean
     */
    public $merchant_phone_dntwork;

    /**
     *
     * @var string
     */
    public $more;

    /**
     *
     * @var string
     */
    public $comment;

    /**
     *
     * @var string
     */
    public $date_create;

    /**
     *
     * @var string
     */
    public $date_closed;

    /**
     *
     * @var int
     */
    public $closed;

    public function initialize() {
        $this->setSource("error_report");

        $this->belongsTo("merchant_id", Merchant::class, "id");
    }

    /**
     * Возвращает либо закрытые ($closed = 1)
     * либо открытые ($closed = 0) ошибки
     *
     * @param int $closed
     * @return ErrorReport
     */
    public static function findAllErrorReports($closed) {
        $errors_criteria = ErrorReport::query()
                ->columns([
                    'id',
                    'date_create',
                    'date_closed',
                    'merchant_name',
                ])
                ->where("closed = :closed:", ['closed' => $closed]);

        $errors = $errors_criteria->execute();
        return $errors;
    }

    /**
     *
     * @return Merchant|null
     */
    public function getMerchant() {
        return $this->getRelated(Merchant::class);
    }

    /**
     *
     * @param string $comment
     * @return $this
     */
    public function closeErrorReport($comment) {
        $this->closed = 1;
        $this->comment = $comment;
        $this->date_closed = date("Y-m-d H:i:s", time());
        $this->save();

        return $this;
    }

}
