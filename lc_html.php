<?php
$options = get_option('loan_calculator_options');

$default_loan_amount = 2500;
$min_loan_amount = $options['min_loan_amount'] ?? 100;
$max_loan_amount = $options['max_loan_amount'] ?? 250000;

$default_interest_rate = 5;
$interest_rate = $options['interest_rate'] ?? 5;
$max_interest_rate = $options['max_interest_rate'] ?? 15;

$default_loan_term = 24;
$min_loan_term = $options['min_loan_term'] ?? 1;
$max_loan_term = $options['max_loan_term'] ?? 360;
$apply_show = $options['apply_show'] ?? 0;
$image_url = !isset($options['image_url']) || !$options['image_url'] ? plugins_url('images/accounting.png', __FILE__):$options['image_url'];
?>
<?php ob_start(); ?>
<!--[if IE]>
<script>alert('Не рабoти с Internet Explorer! За по-добро качество и функционалност на сайта, моля обновете вашия браузър или изтеглете нов.')</script>
<![endif]-->
<div class="lc_loan_container">
  <div class="lc_card">
    <div class="lc_card--content">
      <div id="loan-section">
        <div class="lc_input--title">
          <h4>Размер на кредит</h4>
          <div class="bordered lc_result" id="loan-border">
            <input class="lc_input--result" id="loan-result" type="number" max="<?= $max_loan_amount ?>" value="<?= $default_loan_amount ?>"/>
            <span style="border-left: 1px solid #999; padding-left: 0.5rem">лв.</span>
          </div>
        </div>
        <div>
          <input class="lc_input--range" id="loan" max="<?= $max_loan_amount ?>" min="<?= $min_loan_amount ?>" step="50" type="range" value="<?= $default_loan_amount ?>"/>
        </div>
        <div class="lc_range_labels">
          <p><?= number_format((int)$min_loan_amount, 0,'.', ' ') ?> лв.</p>
          <p><?= number_format((int)$max_loan_amount, 0,'.', ' ') ?> лв.</p>
        </div>
      </div>
      <div id="period-section">
        <div class="lc_input--title">
          <h4>Срок на кредита</h4>
          <div class="bordered lc_result" id="period-border">
            <input class="lc_input--result" id="period-result" type="number" value="<?= $default_loan_term ?>"/>
            <span style="border-left: 1px solid #999; padding-left: 0.5rem">мес.</span>
          </div>
        </div>
        <div>
          <input class="lc_input--range" id="period" max="<?= $max_loan_term ?>" min="<?= $min_loan_term ?>" step="1" type="range" value="<?= $default_loan_term ?>"/>
        </div>
        <div class="lc_range_labels">
          <p><?= $min_loan_term ?> мес.</p>
          <p><?= $max_loan_term ?> мес.</p>
        </div>
      </div>
      <div id="interest-section">
        <div class="lc_input--title">
          <h4>Лихвен процент</h4>
          <div class="bordered lc_result" id="interest-border">
            <input class="lc_input--result" id="interest-result" size="3" type="number" value="<?= $default_interest_rate ?>"/>
            <span style="border-left: 1px solid #999; padding-left: 0.5rem">%</span>
          </div>
        </div>
        <div>
          <input class="lc_input--range" id="interest" max="<?= $max_interest_rate ?>" min="<?= $interest_rate ?>" step="0.1" type="range" value="<?= $default_interest_rate ?>"/>
        </div>
        <div class="lc_range_labels">
          <p><?= $interest_rate ?> %</p>
          <p><?= $max_interest_rate ?> %</p>
        </div>
      </div>
    </div>
  </div>
  <!--  calculation section-->
  <div class="lc_loan--calculation">
    <div class="lc_card--content lc_flex lc_flex-column lc_justify-center lc_items-center">
      <div style="text-align: center; padding: 30px 20px">
<!--        <img alt="logo" height="100" src="--><?php //= plugins_url('images/accounting.png', __FILE__); ?><!--" width="100" >-->
        <img alt="logo" height="100" src="<?= $image_url ?>" width="100" >
        <div style="font-size: 0.85rem; color:#949494; padding: 5px 0">
          Месечна вноска: <span id="period-value"></span>
        </div>
        <h2 class="lc_calculation--monthly-value" id="monthly-value">
        </h2>
      </div>
      <div class="lc_calculation--subcard">
        <p style="display: flex; justify-content: space-between;">
          <span style="font-size: 0.85rem;  color:rgba(0,0,0,.87)">Размер на кредита:</span>
          <span style="font-size: 1.175rem; font-weight: bold" id="loan-value"></span>
        </p>
        <p style="display: flex; justify-content: space-between">
          <span style="font-size: 0.85rem ; color:rgba(0,0,0,.87)">Лихвен процент:</span>
          <span style="font-size: 1.175rem; font-weight: bold" id="interest-value"></span>
        </p>
        <p style="display: flex; justify-content: space-between">
          <span style="font-size: 0.85rem ; color:rgba(0,0,0,.87)">Обща дължима сума:</span>
          <span style="font-size: 1.175rem; font-weight: bold" id="total-value"></span>
        </p>
      </div>
      <?php if((int) $apply_show === 1 ): ?>
      <div style="text-align: center; margin: 20px 0">
        <a href="<?= $options['apply_url'] ?? site_url('about') ?>" class="blue-button">Кандидатствай</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="<?php echo plugins_url( "assets/js/wNumb.min.js", __FILE__ ); ?>"></script>
<script>
    const result = document.getElementById('loan-result');
    const loan = document.getElementById('loan');

    const Format = wNumb({
        decimals: 2,
        thousand: ' ',
        suffix: ' лв.',
    });

    result.addEventListener('change', function (e) {
        loan.value = result.value;
        initRangeEl();
    })

    const allNumbers = document.querySelectorAll('input[type=number]');
    const rangeInputs = document.querySelectorAll('input[type=range]');

    allNumbers.forEach((numberInput, index) => {
        initRangeEl();

        numberInput.addEventListener('change', function (e) {
            rangeInputs[index].value = e.target.value;
            initRangeEl();
            validateNumber(numberInput.value,numberInput.id)

        });
    })

    function calculateLoan() {
        const loanAmount = document.getElementById("loan").value;
        const interestRate = document.getElementById("interest").value;
        const loanTerm = document.getElementById("period").value;

        let monthlyInterestRate = (interestRate / 100) / 12;
        let power = Math.pow((1 + monthlyInterestRate), loanTerm);
        let monthlyPayment = (loanAmount * power * monthlyInterestRate) / (power - 1);

        document.getElementById("monthly-value").innerHTML = Format.to(monthlyPayment);
        document.getElementById("period-value").innerHTML = `(${loanTerm} месеца)`;
        document.getElementById("interest-value").innerHTML = `${interestRate}%`;
        document.getElementById("total-value").innerHTML = Format.to(monthlyPayment * loanTerm);
        document.getElementById("loan-value").innerHTML = Format.to(Number(loanAmount));

    }

    /**
     * Sniffs for Older Edge or IE,
     * more info here:
     * https://stackoverflow.com/q/31721250/3528132
     */
    function isOlderEdgeOrIE() {
        return (
            window.navigator.userAgent.indexOf("MSIE ") > -1 ||
            !!navigator.userAgent.match(/Trident.*rv\:11\./) ||
            window.navigator.userAgent.indexOf("Edge") > -1
        );
    }

    function valueTotalRatio(value, min, max) {
        return ((value - min) / (max - min)).toFixed(2);
    }

    function getLinearGradientCSS(ratio, leftColor, rightColor) {
        return [
            '-webkit-gradient(',
            'linear, ',
            'left top, ',
            'right top, ',
            'color-stop(' + ratio + ', ' + leftColor + '), ',
            'color-stop(' + ratio + ', ' + rightColor + ')',
            ')'
        ].join('');
    }

    function updateRangeEl(rangeEl) {
        const ratio = valueTotalRatio(rangeEl.value, rangeEl.min, rangeEl.max);
        rangeEl.style.backgroundImage = getLinearGradientCSS(ratio, '#0050b2', '#c5c5c5');
    }

    function initRangeEl() {
        const rangeEl = document.querySelectorAll('input[type=range]');
        const textEl = document.querySelectorAll('input[type=number]');

        rangeEl.forEach((rangeEl, index) => {
            updateRangeEl(rangeEl);
            calculateLoan();
            rangeEl.addEventListener("input", function (e) {
                updateRangeEl(e.target);
                textEl[index].value = e.target.value;
                validateNumber(rangeEl.value, rangeEl.id)
                calculateLoan();
            });
        });

    }

    function validateNumber(num, id){
        const reg = /^[^-]*/g;
        let match = (id.match(reg));
        let element;
        let n;

        switch (match[0]){
            case 'loan':
                element = document.getElementById('loan-border');
                n = <?= $max_loan_amount?>;
                break;
            case 'period':
                element = document.getElementById('period-border');
                n = <?= $max_loan_term?>;
                break;
            case 'interest':
                element = document.getElementById('interest-border');
                n = <?= $max_interest_rate?>;
                break;
        }

        element.style.removeProperty('border-color');

        if(Number(num)>n){
            element.style.borderColor = 'red';
        }

    }

    initRangeEl();
    calculateLoan();

</script>

<?php return ob_get_clean(); ?>

