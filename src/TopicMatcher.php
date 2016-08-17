<?php

namespace BinSoul\Net\Mqtt;

/**
 * Matches a topic filter with an actual topic.
 *
 * @author  Alin Eugen Deac <ade@vestergaardcompany.com>
 */
class TopicMatcher
{
    /**
     * Check if the given topic matches the filter.
     *
     * @param string $filter e.g. A/B/+, A/B/#
     * @param string $topic  e.g. A/B/C, A/B/foo/bar/baz
     *
     * @return bool true if topic matches the pattern
     */
    public function matches($filter, $topic)
    {
        // Created by Steffen (https://github.com/kernelguy)
        $tokens = explode('/', $filter);
        $re = [];
        $c = count($tokens);
        for ($i = 0; $i < $c; ++$i) {
            $t = $tokens[$i];
            switch ($t) {
                case '+':
                    $re[] = '[^/#\+]*';
                    break;

                case '#':
                    if ($i == 0) {
                        $re[] = '[^\+\$]*';
                    } else {
                        $re[] = '[^\+]*';
                    }
                    break;

                default:
                    $re[] = str_replace('+', '\+', $t);
                    break;
            }
        }

        $re = implode('/', $re);
        $re = str_replace('$', '\$', $re);
        $re = '^'.$re.'$';

        return preg_match(';'.$re.';', $topic) === 1;
    }
}
