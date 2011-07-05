<?php

/**
 * NCL NameCase Ukranian Language
 * 
 * Клас, которые позволяет склонять украинские Имена, Фамили Отчества по падежам.
 * 
 * @license Dual licensed under the MIT or GPL Version 2 licenses.
 * @author Андрей Чайка http://seagull.net.ua/ bymer3@gmail.com
 * @version 0.4 05.07.2011 
 * 
 */
require_once dirname(__FILE__) . '/NCL.NameCase.core.php';

class NCLNameCaseUa extends NCLNameCaseCore implements NCLNameCaseInterface
{
    /*
     * @static string
     * Количество падежов
     */

    protected $CaseCount = 7;

    /*
     * @static string
     * Список гласных
     */
    private $vowels = "аеиоуіїєюя";

    /*
     * @static string
     * Список согласных
     */
    private $consonant = "бвгджзйклмнпрстфхцчшщ";

    /*
     * @static string
     * Шиплячі приголосні
     */
    private $shyplyachi = "жчшщ";
    private $neshyplyachi = "бвгдзклмнпрстфхц";
    private $myaki = 'ьюяєї';
    private $gubni = 'мвпбф';



    /*
     * Чергування г к х —» з ц с
     * 
     * @return boolean
     */

    private function inverseGKH($letter)
    {
        switch ($letter)
        {
            case 'г': return 'з';
            case 'к': return 'ц';
            case 'х': return 'с';
        }
        return $letter;
    }

    /*
     * Чергування к —» ч
     * 
     * @return boolean
     */

    private function inverse2($letter)
    {
        switch ($letter)
        {
            case 'к': return 'ч';
            case 'г': return 'ж';
        }
        return $letter;
    }

    /*
     * Визначення групи для іменників 2-ї відміни
     * 1 - тверда
     * 2 - мішана
     * 3 - м’яка
     * 
     * Правило:
     * Іменники з основою на твердий нешиплячий належать до твердої групи: береза, дорога, Дніпро, шлях, віз, село, яблуко.
     * Іменники з основою на твердий шиплячий належать до мішаної групи: пожеж-а, пущ-а, тиш-а, алич-а, вуж, кущ, плющ, ключ, плече, прізвище.
     * Іменники з основою на будь-який м'який чи пом'якше­ний належать до м'якої групи: земля [земл'а], зоря [зор'а], армія [арм'ійа], сім'я [с'імйа], серпень, фахівець, трамвай, су­зір'я [суз'ірйа], насіння [насін'н'а], узвишшя Іузвиш'ш'а
     * 
     * @return integer
     */

    private function detect2Group($word)
    {
        $osnova = $word;
        $stack = array();
        //Ріжемо слово поки не зустрінемо приголосний і записуемо в стек всі голосні які зустріли
        while ($this->in($this->substr($osnova, -1, 1), $this->vowels . 'ь'))
        {
            $stack[] = $this->substr($osnova, -1, 1);
            $osnova = $this->substr($osnova, 0, $this->strlen($osnova) - 1);
        }
        $stacksize = count($stack);
        $Last = 'Z'; //нульове закінчення
        if ($stacksize)
        {
            $Last = $stack[count($stack) - 1];
        }

        $osnovaEnd = $this->substr($osnova, -1, 1);
        if ($this->in($osnovaEnd, $this->neshyplyachi) and !$this->in($Last, $this->myaki))
        {
            return 1;
        }
        elseif ($this->in($osnovaEnd, $this->shyplyachi) and !$this->in($Last, $this->myaki))
        {
            return 2;
        }
        else
        {
            return 3;
        }
    }

    /*
     * Повертає перший з кінця голосний
     * 
     * @return char
     */

    private function FirstLastVowel($word, $vowels)
    {
        $length = $this->strlen($word);
        for ($i = $length - 1; $i > 0; $i--)
        {
            $char = $this->substr($word, $i, 1);
            if ($this->in($char, $vowels))
            {
                return $char;
            }
        }
    }

    /*
     * Повертає основу слова
     * 
     * @return boolean
     */

    private function getOsnova($word)
    {
        $osnova = $word;
        //Ріжемо слово поки не зустрінемо приголосний
        while ($this->in($this->substr($osnova, -1, 1), $this->vowels . 'ь'))
        {
            $osnova = $this->substr($osnova, 0, $this->strlen($osnova) - 1);
        }
        return $osnova;
    }

    /**
     * Українські чоловічі та жіночі імена, що в називному відмінку однини закінчуються на -а (-я), 
     * відмінються як відповідні іменники І відміни.  
     * - Примітка 1. Кінцеві приголосні основи г, к, х у жіночих іменах 
     *   у давальному та місцевому відмінках однини перед закінченням -і 
     *   змінюються на з, ц, с: Ольга - Ользі, Палажка - Палажці, Солоха - Солосі.
     * - Примітка 2. У жіночих іменах типу Одарка, Параска в родовому відмінку множини 
     *   в кінці основи між приголосними з'являється звук о: Одарок, Парасок 
     * @return boolean 
     */
    protected function manRule1()
    {
        //Предпоследний символ
        $BeforeLast = $this->Last(2, 1);

        //Останні літера або а
        if ($this->Last(1) == 'а')
        {
            $this->wordForms($this->workingWord, array($BeforeLast . 'и', $this->inverseGKH($BeforeLast) . 'і', $BeforeLast . 'у', $BeforeLast . 'ою', $this->inverseGKH($BeforeLast) . 'і', $BeforeLast . 'о'), 2);
            $this->Rule(101);
            return true;
        }
        //Остання літера я
        elseif ($this->Last(1) == 'я')
        {
            //Перед останньою літерою стоїть я
            if ($BeforeLast == 'і')
            {
                $this->wordForms($this->workingWord, array('ї', 'ї', 'ю', 'єю', 'ї', 'є'), 1);
                $this->Rule(102);
                return true;
            }
            else
            {
                $this->wordForms($this->workingWord, array($BeforeLast . 'і', $this->inverseGKH($BeforeLast) . 'і', $BeforeLast . 'ю', $BeforeLast . 'ею', $this->inverseGKH($BeforeLast) . 'і', $BeforeLast . 'е'), 2);
                $this->Rule(103);
                return true;
            }
        }
        return false;
    }

    /**
     * Імена, що в називному відмінку закінчуються на -р, у родовому мають закінчення -а: 
     * Віктор - Віктора, Макар - Макара, але: Ігор - Ігоря, Лазар - Лазаря.
     * @return boolean 
     */
    protected function manRule2()
    {
        if ($this->Last(1) == 'р')
        {
            if ($this->inNames($this->workingWord, array('Ігор', 'Лазар')))
            {
                $this->wordForms($this->workingWord, array('я', 'еві', 'я', 'ем', 'еві', 'е'));
                $this->Rule(201);
                return true;
            }
            else
            {
                $osnova = $this->workingWord;
                if ($this->substr($osnova, -2, 1) == 'і')
                {
                    $osnova = $this->substr($osnova, 0, $this->strlen($osnova) - 2) . 'о' . $this->substr($osnova, -1, 1);
                }
                $this->wordForms($osnova, array('а', 'ові', 'а', 'ом', 'ові', 'е'));
                $this->Rule(202);
                return true;
            }
        }
        return false;
    }

    /**
     * Українські чоловічі імена, що в називному відмінку однини закінчуються на приголосний та -о, 
     * відмінюються як відповідні іменники ІІ відміни.
     * @return boolean 
     */
    protected function manRule3()
    {
        //Предпоследний символ
        $BeforeLast = $this->Last(2, 1);

        if ($this->in($this->Last(1), $this->consonant . 'оь'))
        {
            $group = $this->detect2Group($this->workingWord);
            $osnova = $this->getOsnova($this->workingWord);
            //В іменах типу Антін, Нестір, Нечипір, Прокіп, Сидір, Тиміш, Федір голосний і виступає тільки в 
            //називному відмінку, у непрямих - о: Антона, Антонові                           
            //Чергування і -» о всередині
            $osLast = $this->substr($osnova, -1, 1);
            if ($osLast != 'й' and $this->substr($osnova, -2, 1) == 'і' and !$this->in($this->substr($this->strtolower($osnova), -4, 4), array('світ', 'цвіт')) and !$this->inNames($this->workingWord, 'Гліб'))
            {
                $osnova = $this->substr($osnova, 0, $this->strlen($osnova) - 2) . 'о' . $this->substr($osnova, -1, 1);
            }


            //Випадання букви е при відмінюванні слів типу Орел
            if ($this->substr($osnova, 0, 1) == 'О' and $this->FirstLastVowel($osnova, $this->vowels . 'гк') == 'е' and $this->Last(2) != 'сь')
            {
                $delim = $this->strrpos($osnova, 'е');
                $osnova = $this->substr($osnova, 0, $delim) . $this->substr($osnova, $delim + 1, $this->strlen($osnova) - $delim);
            }


            if ($group == 1)
            {
                //Тверда група
                //Слова що закінчуються на ок
                if ($this->Last(2) == 'ок' and $this->Last(3) != 'оок')
                {
                    $this->wordForms($this->workingWord, array('ка', 'кові', 'ка', 'ком', 'кові', 'че'), 2);
                    $this->Rule(30101);
                    return true;
                }
                //Російські прізвища на ов, ев, єв
                elseif ($this->in($this->Last(2), array('ов', 'ев', 'єв')) and !$this->inNames($this->workingWord, array('Лев', 'Остромов')))
                {
                    $this->wordForms($osnova, array($osLast . 'а', $osLast . 'у', $osLast . 'а', $osLast . 'им', $osLast . 'у', $this->inverse2($osLast) . 'е'), 1);
                    $this->Rule(30102);
                    return true;
                }
                //Російські прізвища на ін
                elseif ($this->in($this->Last(2), array('ін')))
                {
                    $this->wordForms($this->workingWord, array('а', 'у', 'а', 'ом', 'у', 'е'));
                    $this->Rule(30103);
                    return true;
                }
                else
                {
                    $this->wordForms($osnova, array($osLast . 'а', $osLast . 'ові', $osLast . 'а', $osLast . 'ом', $osLast . 'ові', $this->inverse2($osLast) . 'е'), 1);
                    $this->Rule(301);
                    return true;
                }
            }
            if ($group == 2)
            {
                //Мішана група
                $this->wordForms($osnova, array('а', 'еві', 'а', 'ем', 'еві', 'е'));
                $this->Rule(302);
                return true;
            }
            if ($group == 3)
            {
                //М’яка група
                //Соловей
                if ($this->Last(2) == 'ей' and $this->in($this->Last(3, 1), $this->gubni))
                {
                    $osnova = $this->substr($this->workingWord, 0, $this->strlen($this->workingWord) - 2) . '’';
                    $this->wordForms($osnova, array('я', 'єві', 'я', 'єм', 'єві', 'ю'));
                    $this->Rule(303);
                    return true;
                }
                elseif ($this->Last(1) == 'й' or $BeforeLast == 'і')
                {
                    $this->wordForms($this->workingWord, array('я', 'єві', 'я', 'єм', 'єві', 'ю'), 1);
                    $this->Rule(304);
                    return true;
                }
                //Слова що закінчуються на ець
                elseif ($this->Last(3) == 'ець')
                {
                    $this->wordForms($this->workingWord, array('ця', 'цеві', 'ця', 'цем', 'цеві', 'цю'), 3);
                    $this->Rule(305);
                    return true;
                }
                //Слова що закінчуються на єць яць
                elseif ($this->in($this->Last(3), array('єць', 'яць')))
                {
                    $this->wordForms($this->workingWord, array('йця', 'йцеві', 'йця', 'йцем', 'йцеві', 'йцю'), 3);
                    $this->Rule(306);
                    return true;
                }
                else
                {
                    $this->wordForms($osnova, array('я', 'еві', 'я', 'ем', 'еві', 'ю'));
                    $this->Rule(305);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Якщо слово закінчується на і, то відмінюємо як множину
     * @return boolean 
     */
    protected function manRule4()
    {
        if ($this->Last(1) == 'і')
        {
            $this->wordForms($this->workingWord, array('их', 'им', 'их', 'ими', 'их', 'і'), 1);
            $this->Rule(4);
            return true;
        }
        return false;
    }

    /**
     * Якщо слово закінчується на ий або ой
     * @return boolena 
     */
    protected function manRule5()
    {
        if ($this->in($this->Last(2), array('ий', 'ой')))
        {
            $this->wordForms($this->workingWord, array('ого', 'ому', 'ого', 'им', 'ому', 'ий'), 2);
            $this->Rule(5);
            return true;
        }
        return false;
    }

    /**
     * Українські чоловічі та жіночі імена, що в називному відмінку однини закінчуються на -а (-я), 
     * відмінються як відповідні іменники І відміни.  
     * - Примітка 1. Кінцеві приголосні основи г, к, х у жіночих іменах 
     *   у давальному та місцевому відмінках однини перед закінченням -і 
     *   змінюються на з, ц, с: Ольга - Ользі, Палажка - Палажці, Солоха - Солосі.
     * - Примітка 2. У жіночих іменах типу Одарка, Параска в родовому відмінку множини 
     *   в кінці основи між приголосними з'являється звук о: Одарок, Парасок 
     * @return boolean 
     */
    protected function womanRule1()
    {
        //Предпоследний символ
        $BeforeLast = $this->Last(2, 1);

        //Якщо закінчується на ніга -» нога
        if ($this->Last(4) == 'ніга')
        {
            $osnova = $this->substr($this->workingWord, 0, $this->strlen($this->workingWord) - 3) . 'о';
            $this->wordForms($osnova, array('ги', 'зі', 'гу', 'гою', 'зі', 'го'));
            $this->Rule(101);
            return true;
        }

        //Останні літера або а
        elseif ($this->Last(1) == 'а')
        {
            $this->wordForms($this->workingWord, array($BeforeLast . 'и', $this->inverseGKH($BeforeLast) . 'і', $BeforeLast . 'у', $BeforeLast . 'ою', $this->inverseGKH($BeforeLast) . 'і', $BeforeLast . 'о'), 2);
            $this->Rule(101);
            return true;
        }
        //Остання літера я
        elseif ($this->Last(1) == 'я')
        {

            if ($this->in($BeforeLast, $this->vowels))
            {
                $this->wordForms($this->workingWord, array('ї', 'ї', 'ю', 'єю', 'ї', 'є'), 1);
                $this->Rule(103);
                return true;
            }
            else
            {
                $this->wordForms($this->workingWord, array($BeforeLast . 'і', $this->inverseGKH($BeforeLast) . 'і', $BeforeLast . 'ю', $BeforeLast . 'ею', $this->inverseGKH($BeforeLast) . 'і', $BeforeLast . 'е'), 2);
                $this->Rule(102);
                return true;
            }
        }
        return false;
    }

    /**
     * Українські жіночі імена, що в називному відмінку однини закінчуються на приголосний, 
     * відмінюються як відповідні іменники ІІІ відміни
     * @return boolean 
     */
    protected function womanRule2()
    {
        if ($this->in($this->Last(1), $this->consonant . 'ь'))
        {
            $osnova = $this->getOsnova($this->firstName);
            $apostrof = '';
            $duplicate = '';
            $osLast = $this->substr($osnova, -1, 1);
            $osBeforeLast = $this->substr($osnova, -2, 1);

            //Чи треба ставити апостроф
            if ($this->in($osLast, 'мвпбф') and ($this->in($osBeforeLast, $this->vowels)))
            {
                $apostrof = '’';
            }

            //Чи треба подвоювати
            if ($this->in($osLast, 'дтзсцлн'))
            {
                $duplicate = $osLast;
            }


            //Відмінюємо
            if ($this->Last(1) == 'ь')
            {
                $this->wordForms($osnova, array('і', 'і', 'ь', $duplicate . $apostrof . 'ю', 'і', 'е'));
                $this->Rule(402);
                return true;
            }
            else
            {
                $this->wordForms($osnova, array('і', 'і', '', $duplicate . $apostrof . 'ю', 'і', 'е'));
                $this->Rule(401);
                return true;
            }
        }
        return false;
    }

    /**
     * Якщо слово на ськ або це російське прізвище
     * @return boolean 
     */
    protected function womanRule3()
    {
        //Предпоследний символ
        $BeforeLast = $this->Last(2, 1);

        //Донская
        if ($this->Last(2) == 'ая')
        {
            $this->wordForms($this->workingWord, array('ої', 'ій', 'ую', 'ою', 'ій', 'ая'), 2);
            $this->Rule(301);
            return true;
        }

        //Ті що на ськ
        if ($this->Last(1) == 'а' and ($this->in($this->Last(3, 2), array('ов', 'ев', 'єв', 'ив', 'ьк', 'тн', 'рн', 'ин'))))
        {
            $this->wordForms($this->workingWord, array($BeforeLast . 'ої', $BeforeLast . 'ій', $BeforeLast . 'у', $BeforeLast . 'ою', $BeforeLast . 'ій', $BeforeLast . 'о'), 2);
            $this->Rule(302);
            return true;
        }

        return false;
    }

    /**
     * Функция, которая склоняет имя записаное в $this->firstName, по правилам склонения мужских имен.
     * @return boolean
     */
    protected function manFirstName()
    {

        $this->setWorkingWord($this->firstName);

        if ($this->RulesChain('man', array(1, 2, 3)))
        {
            $this->frule = $this->lastRule;
            $this->firstResult = $this->lastResult;
        }
        else
        {
            $this->makeFirstTheSame();
        }
    }

    /**
     * Функция, которая склоняет имя записаное в $this->firstName, по правилам склонения женских имен.
     * 
     * @return boolean
     */
    protected function womanFirstName()
    {
        $this->setWorkingWord($this->firstName);

        if ($this->RulesChain('woman', array(1, 2)))
        {
            $this->frule = $this->lastRule;
            $this->firstResult = $this->lastResult;
        }
        else
        {
            $this->makeFirstTheSame();
        }
    }

    /*
     * Функция, которая склоняет фамилию записаное в $this->secondName, по правилам склонения мужских фамилий.
     * 
     * @return boolean
     */

    protected function manSecondName()
    {
        $this->setWorkingWord($this->secondName);

        if ($this->RulesChain('man', array(5, 1, 2, 3, 4)))
        {
            $this->srule = $this->lastRule;
            $this->secondResult = $this->lastResult;
        }
        else
        {
            $this->makeSecondTheSame();
        }
    }

    /*
     * Функция, которая склоняет фамилию записаное в $this->secondName, по правилам склонения женских фамилий.
     * 
     * @return boolean
     */

    protected function womanSecondName()
    {
        $this->setWorkingWord($this->secondName);

        if ($this->RulesChain('woman', array(3, 1)))
        {
            $this->srule = $this->lastRule;
            $this->secondResult = $this->lastResult;
        }
        else
        {
            $this->makeSecondTheSame();
        }
    }

    /*
     * Функция, которая склоняет отчество записаное в $this->secondName, по правилам склонения мужских отчеств.
     * 
     * @return boolean
     */

    protected function manFatherName()
    {
        $this->setWorkingWord($this->fatherName);
        if ($this->in($this->Last(2), array('ич', 'іч')))
        {
            $this->wordForms($this->workingWord, array('а', 'у', 'а', 'ем', 'у', 'у'));
            $this->fatherResult = $this->lastResult;
            return true;
        }
        else
        {
            $this->makeFatherTheSame();
            return false;
        }
    }

    /*
     * Функция, которая склоняет отчество записаное в $this->fatherName, по правилам склонения женских отчеств.
     * 
     * @return boolean
     */

    protected function womanFatherName()
    {
        $this->setWorkingWord($this->fatherName);
        if ($this->in($this->Last(3), array('вна')))
        {
            $this->wordForms($this->workingWord, array('и', 'і', 'у', 'ою', 'і', 'о'), 1);
            $this->fatherResult = $this->lastResult;
            return true;
        }
        else
        {
            $this->makeFatherTheSame();
            return false;
        }
    }

    /*
     * Автоматическое определение пола
     * @return void
     */

    protected function genderDetect()
    {

        //$this->gender = NCL::$MAN; // мужчина
        if (!$this->gender)
        {
            //Определение пола по отчеству
            if (isset($this->fatherName) and $this->fatherName)
            {
                $LastTwo = mb_substr($this->fatherName, -2, 2, 'utf-8');
                if ($LastTwo == 'ич')
                {
                    $this->gender = NCL::$MAN; // мужчина
                    return true;
                }
                if ($LastTwo == 'на')
                {
                    $this->gender = NCL::$WOMAN; // женщина
                    return true;
                }
            }
            $man = 0; //Мужчина
            $woman = 0; //Женщина

            $FLastSymbol = mb_substr($this->firstName, -1, 1, 'utf-8');
            $FLastTwo = mb_substr($this->firstName, -2, 2, 'utf-8');
            $FLastThree = mb_substr($this->firstName, -3, 3, 'utf-8');
            $FLastFour = mb_substr($this->firstName, -4, 4, 'utf-8');

            $SLastSymbol = mb_substr($this->secondName, -1, 1, 'utf-8');
            $SLastTwo = mb_substr($this->secondName, -2, 2, 'utf-8');
            $SLastThree = mb_substr($this->secondName, -3, 3, 'utf-8');

            //Если нет отчества, то определяем по имени и фамилии, будем считать вероятность
            if (isset($this->firstName) and $this->firstName)
            {
                //Попробуем выжать максимум из имени
                //Если имя заканчивается на й, то скорее всего мужчина
                if ($FLastSymbol == 'й')
                {
                    $man+=0.9;
                }
                if (in_array($FLastTwo, array('он', 'ов', 'ав', 'ам', 'ол', 'ан', 'рд', 'мп', 'ко', 'ло')))
                {
                    $man+=0.5;
                }
                if (in_array($FLastThree, array('бов', 'нка', 'яра', 'ила')))
                {
                    $woman+=0.5;
                }
                if ($this->in($FLastSymbol, $this->consonant))
                {
                    $man+=0.01;
                }
                if ($FLastSymbol == 'ь')
                {
                    $man+=0.02;
                }

                if (in_array($FLastTwo, array('дь')))
                {
                    $woman+=0.1;
                }

                if (in_array($FLastThree, array('ель', 'бов')))
                {
                    $woman+=0.4;
                }
            }

//            $man*=1.2;
//            $woman*=1.2;

            if (isset($this->secondName) and $this->secondName)
            {
                if (in_array($SLastTwo, array('ов', 'ин', 'ев', 'єв', 'ін', 'їн', 'ий', 'їв', 'ів', 'ой', 'ей')))
                {
                    $man+=0.4;
                }

                if (in_array($SLastThree, array('ова', 'ина', 'ева', 'єва', 'іна')))
                {
                    $woman+=0.4;
                }

                if (in_array($SLastTwo, array('ая')))
                {
                    $woman+=0.4;
                }
            }

            //Теперь смотрим, кто больше набрал
            if ($man > $woman)
            {
                $this->gender = NCL::$MAN;
            }
            else
            {
                $this->gender = NCL::$WOMAN;
            }
        }
        return true;
    }

    /*
     * Определение текущее слово есть фамилией, именем или отчеством
     * @return integer $number - 1-фамили 2-имя 3-отчество
     */

    protected function detectNamePart($namepart)
    {
        $LastSymbol = mb_substr($namepart, -1, 1, 'utf-8');
        $LastTwo = mb_substr($namepart, -2, 2, 'utf-8');
        $LastThree = mb_substr($namepart, -3, 3, 'utf-8');
        $LastFour = mb_substr($namepart, -4, 4, 'utf-8');

        //Считаем вероятность
        $first = 0;
        $second = 0;
        $father = 0;

        //если смахивает на отчество
        if (in_array($LastThree, array('вна', 'чна', 'ліч')) or in_array($LastFour, array('ьмич', 'ович')))
        {
            $father+=3;
        }

        //Похоже на имя
        if (in_array($LastThree, array('тин' /* {endings_sirname3} */)) or in_array($LastFour, array('ьмич', 'юбов', 'івна', 'явка', 'орив', 'кіян' /* {endings_sirname4} */)))
        {
            $first+=0.5;
        }

        //Исключения
        if (in_array($namepart, array('Лев', 'Гаїна', 'Афіна', 'Антоніна', 'Ангеліна', 'Альвіна', 'Альбіна', 'Аліна', 'Павло', 'Олесь')))
        {
            $first+=10;
        }

        //похоже на фамилию
        if (in_array($LastTwo, array('ов', 'ін', 'ев', 'єв', 'ий', 'ин', 'ой', 'ко', 'ук', 'як', 'ца', 'их', 'ик', 'ун', 'ок', 'ша', 'ая', 'га', 'єк', 'аш', 'ив', 'юк', 'ус', 'це', 'ак', 'бр', 'яр', 'іл', 'ів', 'ич', 'сь', 'ей', 'нс', 'яс', 'ер', 'ай', 'ян', 'ах', 'ць', 'ющ', 'іс', 'ач', 'уб', 'ох', 'юх', 'ут', 'ча', 'ул', 'вк', 'зь', 'уц', 'їн' /* {endings_name2} */)))
        {
            $second+=0.4;
        }

        if (in_array($LastThree, array('ова', 'ева', 'єва', 'тих', 'рик', 'вач', 'аха', 'шен', 'мей', 'арь', 'вка', 'шир', 'бан', 'чий', 'іна', 'їна', 'ька', 'ань', 'ива', 'аль', 'ура', 'ран', 'ало', 'ола', 'кур', 'оба', 'оль', 'нта', 'зій', 'ґан', 'іло', 'шта', 'юпа', 'рна', 'бла', 'еїн', 'има', 'мар', 'кар', 'оха', 'чур', 'ниш', 'ета', 'тна', 'зур', 'нір', 'йма', 'орж', 'рба', 'іла', 'лас', 'дід', 'роз', 'аба', 'лест', 'мара', 'обка', 'рока', 'сика', 'одна', 'нчар', 'вата', 'ндар', 'грій' /* {endings_name3} */)))
        {
            $second+=0.4;
        }

        if (in_array($LastFour, array('ьник', 'нчук', 'тник', 'кирь', 'ский', 'шена', 'шина', 'вина', 'нина', 'гана', 'гана', 'хній', 'зюба', 'орош', 'орон', 'сило', 'руба' /* {endings_name4} */)))
        {
            $second+=0.4;
        }


        $max = max(array($first, $second, $father));

        if ($first == $max)
        {
            return 'N';
        }
        elseif ($second == $max)
        {
            return 'S';
        }
        else
        {
            return 'F';
        }
    }

}

?>