<?php

namespace App\Dto;

/**
 * This is autogenerated object, please not't touch it.
 *
 * @version 0.1.0
 */
final class Lead
{
	/**
	 * Участники сделки
	 *
	 * @Symfony\Component\Validator\Constraints\Valid()
	 * @var LeadParticipant[]
	 */
	public $participants = [];

	/**
	 * Продукт
	 *
	 * @var string
	 *
	 * @Symfony\Component\Validator\Constraints\NotNull()
	 * @Symfony\Component\Validator\Constraints\Choice(choices={"mortgage","refinancing"})
	 */
	public $type;

	/**
	 * Нужна аккредитация
	 *
	 * @var bool
	 */
	public $needRealtyAccreditation;

	/**
	 * Военная ипотека
	 *
	 * @var bool
	 */
	public $militaryMortgage;

	/**
	 * Срок кредита (мес.)
	 *
	 * @var int
	 *
	 * @Symfony\Component\Validator\Constraints\NotNull()
	 */
	public $creditPeriod;

	/**
	 * Первоначальный взнос (руб.) (Обязательно при Ипотеке)
	 *
	 * @var int
	 */
	public $initialPayment;

	/**
	 * Остаток кредита (Обязательно при Рефинансировании)
	 *
	 * @var int
	 */
	public $restOfDebt;

	/**
	 * Тип недвижимости
	 *
	 * @var string
	 *
	 * @Symfony\Component\Validator\Constraints\Choice(choices={"apartment","room","house"})
	 */
	public $realtyType;

	/**
	 * Стоимость жилья (руб.)
	 * Обязательно при расчёте по стоимости квартиры
	 *
	 * TODO: сейчас предполагается, что все расчёты производятся только по стоимости жилья, но в будущем будет "Тип расчёты", в завивсимости от этого "realtyPrice" станет опциональным
	 *
	 * @var int
	 *
	 * @Symfony\Component\Validator\Constraints\NotNull()
	 */
	public $realtyPrice;

	/**
	 * @var Address
	 */
	public $realtyAddress;

	/**
	 * Продавец
	 *
	 * @var string
	 *
	 * @Symfony\Component\Validator\Constraints\NotNull()
	 * @Symfony\Component\Validator\Constraints\Choice(choices={"developer","individual","legal","assignationOfLegal","assignationOfIndividual"})
	 */
	public $realtySellerType;

	/**
	 * Использование материнского капитала
	 *
	 * @var bool
	 */
	public $maternityCapital;

	/**
	 * Размер взноса из мат. капитала
	 *
	 * @var int
	 */
	public $maternityCapitalSum;


	/**
	 * @var LeadParticipant $participant
	 *
	 * @return Lead
	 */
	public function addParticipant(LeadParticipant $participant): Lead
	{
		if (!in_array($participant, $this->participants, true)) {
		    $this->participants = $participant;
		}

		return $this;
	}


	/**
	 * @var LeadParticipant $participant
	 *
	 * @return Lead
	 */
	public function removeParticipant(LeadParticipant $participant): Lead
	{
		if (false !== $index = array_search($participant, $this->participants, true)) {
		    unset($this->participants[$index]);
		}

		return $this;
	}
}

