<?php

namespace App\Dto;

/**
 * This is autogenerated object, please not't touch it.
 *
 * @version 0.1.0
 */
final class Mortgage implements BankRequestProductInterface
{
	/**
	 * @var int
	 */
	public $initialPayment;

	/**
	 * @var string
	 */
	public $realtyCity;

	/**
	 * Участники сделки
	 *
	 * @Symfony\Component\Validator\Constraints\Valid()
	 * @var Participant[]
	 */
	public $participants = [];

	/**
	 * Срок кредита
	 *
	 * @var int
	 */
	public $creditPeriod;

	/**
	 * Сумма кредита
	 *
	 * @var int
	 */
	public $creditSum;

	/**
	 * Военная ипотека?
	 *
	 * @var bool
	 */
	public $militaryMortgage;

	/**
	 * Есть материнский капитал?
	 *
	 * @var bool
	 */
	public $maternityCapital;

	/**
	 * Сумма материнского капитала
	 *
	 * @var int
	 */
	public $maternityCapitalSum;

	/**
	 * Продавец недвижимости
	 *
	 * @var string
	 *
	 * @Symfony\Component\Validator\Constraints\Choice(choices={"developer","individual","legal","assignationOfLegal","assignationOfIndividual"})
	 */
	public $realtySellerType;

	/**
	 * @var Address
	 */
	public $realtyAddress;

	/**
	 * Тип недвижимости
	 *
	 * @var string
	 */
	public $realtyType;

	/**
	 * Нужна акредитация объекта?
	 *
	 * @var bool
	 */
	public $needRealtyAccreditation;

	/**
	 * Стоимость недвижки
	 *
	 * @var int
	 */
	public $realtyPrice;

	/**
	 * @var string
	 */
	public $type;


	/**
	 * @var Participant $participant
	 *
	 * @return Mortgage
	 */
	public function addParticipant(Participant $participant): Mortgage
	{
		if (!in_array($participant, $this->participants, true)) {
		    $this->participants = $participant;
		}

		return $this;
	}


	/**
	 * @var Participant $participant
	 *
	 * @return Mortgage
	 */
	public function removeParticipant(Participant $participant): Mortgage
	{
		if (false !== $index = array_search($participant, $this->participants, true)) {
		    unset($this->participants[$index]);
		}

		return $this;
	}
}

