<?php
namespace MathPHP\Statistics;

use MathPHP\Statistics\Average;
use MathPHP\Statistics\Descriptive;
use MathPHP\Probability\Distribution\Table;
use MathPHP\Functions\Map;
use MathPHP\Exception;

/**
 * Functions dealing with random variables.
 *
 * - Central moment
 * - Skewness
 * - Kurtosis
 * - Standard Error of the Mean (SEM)
 * - Confidence interval
 * - Bhattacharyya distance
 *
 * In probability and statistics, a random variable is a variable whose
 * value is subject to variations due to chance.
 * A random variable can take on a set of possible different values
 * (similarly to other mathematical variables), each with an associated
 * probability, in contrast to other mathematical variables.
 *
 * The mathematical function describing the possible values of a random
 * variable and their associated probabilities is known as a probability
 * distribution. Random variables can be discrete, that is, taking any of a
 * specified finite or countable list of values, endowed with a probability
 * mass function, characteristic of a probability distribution; or
 * continuous, taking any numerical value in an interval or collection of
 * intervals, via a probability density function that is characteristic of
 * a probability distribution; or a mixture of both types.
 *
 * https://en.wikipedia.org/wiki/Random_variable
 */
class RandomVariable
{
    /**
     * n-th Central moment
     * A moment of a probability distribution of a random variable about the random variable's mean.
     * It is the expected value of a specified integer power of the deviation of the random variable from the mean.
     * https://en.wikipedia.org/wiki/Central_moment
     *
     *      ∑⟮xᵢ - μ⟯ⁿ
     * μn = ----------
     *          N
     *
     * @param array $X list of numbers (random variable X)
     * @param array $n n-th central moment to calculate
     *
     * @return number n-th central moment
     */
    public static function centralMoment(array $X, $n)
    {
        if (empty($X)) {
            return null;
        }

        $μ         = Average::mean($X);
        $∑⟮xᵢ − μ⟯ⁿ = array_sum(array_map(
            function ($xᵢ) use ($μ, $n) {
                return pow(($xᵢ - $μ), $n);
            },
            $X
        ));
        $N = count($X);
    
        return $∑⟮xᵢ − μ⟯ⁿ / $N;
    }

    /**
     * Popluation skewness
     * A measure of the asymmetry of the probability distribution of a real-valued random variable about its mean.
     * https://en.wikipedia.org/wiki/Skewness
     * http://brownmath.com/stat/shape.htm
     *
     * This method tends to match Excel's SKEW.P function.
     *
     *         μ₃
     * γ₁ = -------
     *       μ₂³′²
     *
     * μ₂ is the second central moment
     * μ₃ is the third central moment
     *
     * @param array $X list of numbers (random variable X)
     *
     * @return number
     */
    public static function populationSkewness(array $X)
    {
        if (empty($X)) {
            return null;
        }

        $μ₃ = self::centralMoment($X, 3);
        $μ₂ = self::centralMoment($X, 2);
    
        $μ₂³′² = pow($μ₂, 3/2);

        return ($μ₃ /  $μ₂³′²);
    }

    /**
     * Sample skewness
     * A measure of the asymmetry of the probability distribution of a real-valued random variable about its mean.
     * https://en.wikipedia.org/wiki/Skewness
     * http://brownmath.com/stat/shape.htm
     *
     * This method tends to match Excel's SKEW function.
     *
     *         μ₃     √(n(n - 1))
     * γ₁ = ------- × -----------
     *       μ₂³′²       n - 2
     *
     * μ₂ is the second central moment
     * μ₃ is the third central moment
     * n is the sample size
     *
     * @param array $X list of numbers (random variable X)
     *
     * @return number
     */
    public static function sampleSkewness(array $X)
    {
        if (empty($X)) {
            return null;
        }

        $n     = count($X);
        $μ₃    = self::centralMoment($X, 3);
        $μ₂    = self::centralMoment($X, 2);

        $μ₂³′² = pow($μ₂, 3/2);

        $√⟮n⟮n − 1⟯⟯ = sqrt($n * ($n - 1));

        return ($μ₃ / $μ₂³′²) * ( $√⟮n⟮n − 1⟯⟯ / ($n - 2) );
    }

    /**
     * Skewness (alternative method)
     * This method tends to match most of the online skewness calculators and examples.
     * https://en.wikipedia.org/wiki/Skewness
     *
     *         1     ∑⟮xᵢ - μ⟯³
     * γ₁ =  ----- × ---------
     *       N - 1       σ³
     *
     * μ is the mean
     * σ³ is the standard deviation cubed, or, the variance raised to the 3/2 power.
     * N is the sample size
     *
     * @param array $X list of numbers (random variable X)
     *
     * @return number
     */
    public static function skewness(array $X)
    {
        if (empty($X)) {
            return null;
        }

        $μ         = Average::mean($X);
        $∑⟮xᵢ − μ⟯³ = array_sum(array_map(
            function ($xᵢ) use ($μ) {
                return pow(($xᵢ - $μ), 3);
            },
            $X
        ));
        $σ³ = pow(Descriptive::standardDeviation($X, Descriptive::SAMPLE), 3);
        $N  = count($X);
    
        return $∑⟮xᵢ − μ⟯³ / ($σ³ * ($N - 1));
    }

    /**
     * Standard Error of Skewness (SES)
     *
     *         _____________________
     *        /      6n(n - 1)
     * SES = / --------------------
     *      √  (n - 2)(n + 1)(n + 3)
     *
     * @param int $n Sample size
     *
     * @return number
     */
    public static function SES(int $n)
    {
        $６n⟮n − 1⟯           = 6 * $n * ($n - 1);
        $⟮n − 2⟯⟮n ＋ 1⟯⟮n ＋ 2⟯ = ($n - 2) * ($n + 1) * ($n + 3);

        return sqrt($６n⟮n − 1⟯ / $⟮n − 2⟯⟮n ＋ 1⟯⟮n ＋ 2⟯);
    }

    /**
     * Excess Kurtosis
     * A measure of the "tailedness" of the probability distribution of a real-valued random variable.
     * https://en.wikipedia.org/wiki/Kurtosis
     *
     *       μ₄
     * γ₂ = ---- − 3
     *       μ₂²
     *
     * μ₂ is the second central moment
     * μ₄ is the fourth central moment
     *
     * @param array $X list of numbers (random variable X)
     *
     * @return number
     */
    public static function kurtosis(array $X)
    {
        if (empty($X)) {
            return null;
        }

        $μ₄  = self::centralMoment($X, 4);
        $μ₂² = pow(self::centralMoment($X, 2), 2);

        return ( $μ₄ / $μ₂² ) - 3;
    }

    /**
     * Is the kurtosis negative? (Platykurtic)
     * Indicates a flat distribution.
     *
     * @param array $X list of numbers (random variable X)
     *
     * @return bool true if platykurtic
     */
    public static function isPlatykurtic(array $X): bool
    {
        return self::kurtosis($X) < 0;
    }

    /**
     * Is the kurtosis postive? (Leptokurtic)
     * Indicates a peaked distribution.
     *
     * @param array $X list of numbers (random variable X)
     *
     * @return bool true if leptokurtic
     */
    public static function isLeptokurtic(array $X): bool
    {
        return self::kurtosis($X) > 0;
    }

    /**
     * Is the kurtosis zero? (Mesokurtic)
     * Indicates a normal distribution.
     *
     * @param array $X list of numbers (random variable X)
     *
     * @return bool true if mesokurtic
     */
    public static function isMesokurtic(array $X): bool
    {
        return self::kurtosis($X) == 0;
    }

    /**
     * Standard Error of Kurtosis (SEK)
     *
     *                ______________
     *               /    (n² - 1)
     * SEK = 2(SES) / --------------
     *             √  (n - 3)(n + 5)
     *
     * @param int $n Sample size
     *
     * @return number
     */
    public static function SEK(int $n)
    {
        $２⟮SES⟯        = 2 * self::SES($n);
        $⟮n² − 1⟯       = $n**2 - 1;
        $⟮n − 3⟯⟮n ＋ 5⟯ = ($n - 3) * ($n + 5);

        return $２⟮SES⟯ * sqrt($⟮n² − 1⟯ / (($n - 3) * ($n + 5)));
    }

    /**
     * Standard error of the mean (SEM)
     * The standard deviation of the sample-mean's estimate of a population mean.
     * https://en.wikipedia.org/wiki/Standard_error
     *
     *       s
     * SEₓ = --
     *       √n
     *
     * s = sample standard deviation
     * n = size (number of observations) of the sample
     *
     * @param array $X list of numbers (random variable X)
     *
     * @return float
     */
    public static function standardErrorOfTheMean(array $X): float
    {
        $s  = Descriptive::standardDeviation($X, Descriptive::SAMPLE);
        $√n = sqrt(count($X));
        return $s / $√n;
    }

    /**
     * SEM - Convenience method for standard error of the mean
     *
     * @param array $X list of numbers (random variable X)
     *
     * @return float
     */
    public static function sem(array $X): float
    {
        return self::standardErrorOfTheMean($X);
    }

    /**
     * Confidence interval
     * Finds CI given a sample mean, sample size, and standard deviation.
     * Uses Z score.
     * https://en.wikipedia.org/wiki/Confidence_interval
     *          σ
     * ci = z* --
     *         √n
     *
     * interval = (μ - ci, μ + ci)
     *
     * Available confidence levels: See Probability\StandardNormalTable::Z_SCORES_FOR_CONFIDENCE_INTERVALS
     *
     * @param number $μ  sample mean
     * @param number $n  sample size
     * @param number $σ  standard deviation
     * @param string $cl confidence level (Ex: 95, 99, 99.5, 99.9, etc.)
     *
     * @return array [ ci, lower_bound, upper_bound ]
     */
    public static function confidenceInterval($μ, $n, $σ, string $cl): array
    {
        $z = Table\StandardNormal::getZScoreForConfidenceInterval($cl);

        $ci = $z * ($σ / sqrt($n));

        $lower_bound = $μ - $ci;
        $upper_bound = $μ + $ci;

        return [
            'ci' => $ci,
            'lower_bound' => $lower_bound,
            'upper_bound' => $upper_bound,
        ];
    }

    /**
     * Sum of squares
     *
     * ∑⟮xᵢ⟯²
     *
     * @param array $numbers
     *
     * @return number
     */
    public static function sumOfSquares(array $numbers)
    {
        if (empty($numbers)) {
            return null;
        }

         $∑⟮xᵢ⟯² = array_sum(Map\Single::square($numbers));

         return $∑⟮xᵢ⟯²;
    }

    /**
     * Sum of squares deviations
     *
     * ∑⟮xᵢ - μ⟯²
     *
     * @param  array  $numbers
     *
     * @return number
     */
    public static function sumOfSquaresDeviations(array $numbers)
    {
        if (empty($numbers)) {
            return null;
        }

        $μ         = Average::mean($numbers);
        $∑⟮xᵢ − μ⟯² = array_sum(array_map(
            function ($xᵢ) use ($μ) {
                return pow(($xᵢ - $μ), 2);
            },
            $numbers
        ));

        return $∑⟮xᵢ − μ⟯²;
    }

    /**
     * Bhattacharyya distance
     * Measures the similarity of two discrete or continuous probability distributions.
     * https://en.wikipedia.org/wiki/Bhattacharyya_distance
     *
     * For probability distributions p and q over the same domain X,
     * the Bhattacharyya distance is defined as:
     *
     * DB(p,q) = -ln(BC(p,q))
     *
     * where BC is the Bhattacharyya coefficient:
     *
     * BC(p,q) = ∑ √(p(x) q(x))
     *          x∈X
     *
     * @param array $p distribution p
     * @param array $q distribution q
     *
     * @return float distance between distributions
     *
     * @throws BadDataException if p and q do not have the same number of elements
     * @throws BadDataException if p and q are not probability distributions that add up to 1
     */
    public static function bhattacharyyaDistance(array $p, array $q)
    {
        // Arrays must have the same number of elements
        if (count($p) !== count($q)) {
            throw new Exception\BadDataException('p and q must have the same number of elements');
        }

        // Probability distributions must add up to 1.0
        if ((array_sum($p) != 1) || (array_sum($q) != 1)) {
            throw new Exception\BadDataException('Distributions p anad q must add up to 1');
        }

        $BC⟮p、q⟯ = array_sum(Map\Single::sqrt(Map\Multi::multiply($p, $q)));

        return -log($BC⟮p、q⟯);
    }

    /**
     * Kullback-Leibler divergence
     * (also known as: discrimination information, information divergence, information gain, relative entropy, KLIC, KL divergence)
     * A measure of the difference between two probability distributions P and Q.
     * https://en.wikipedia.org/wiki/Kullback%E2%80%93Leibler_divergence
     *
     *                       P(i)
     * Dkl(P‖Q) = ∑ P(i) log ----
     *            ⁱ          Q(i)
     *
     *
     *
     * @param  array  $p distribution p
     * @param  array  $q distribution q
     *
     * @return float difference between distributions
     *
     * @throws BadDataException if p and q do not have the same number of elements
     * @throws BadDataException if p and q are not probability distributions that add up to 1
     */
    public static function kullbackLeiblerDivergence(array $p, array $q)
    {
        // Arrays must have the same number of elements
        if (count($p) !== count($q)) {
            throw new Exception\BadDataException('p and q must have the same number of elements');
        }

        // Probability distributions must add up to 1.0
        if ((array_sum($p) != 1) || (array_sum($q) != 1)) {
            throw new Exception\BadDataException('Distributions p anad q must add up to 1');
        }

        $Dkl⟮P‖Q⟯ = array_sum(array_map(
            function ($P, $Q) use ($p, $q) {
                return $P * log($P / $Q);
            },
            $p,
            $q
        ));

        return $Dkl⟮P‖Q⟯;
    }
}
