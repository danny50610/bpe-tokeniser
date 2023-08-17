<?php

namespace Danny50610\BpeTokeniser;

class Encoding
{
    protected $mergeableRanks;

    protected $pattenRegex;

    public function __construct(&$mergeableRanks, $pattenRegex)
    {
        $this->pattenRegex = $pattenRegex;
        $this->mergeableRanks = $mergeableRanks;
    }

    public function encodeOrdinary(string $text): array
    {
        $result = [];
        preg_match_all($this->pattenRegex, $text, $matches);
        foreach ($matches[0] as $match) {
            var_dump($match);

            $token = $this->mergeableRanks[$match] ?? null;
            if (!is_null($token)) {
                $result[] = $token;
            } else {
                $resultList = $this->bytePairEncode($match, $this->mergeableRanks);
                foreach ($resultList as $item) {
                    $result[] = $item;
                }
            }
        }

        return $result;
    }

    protected function bytePairEncode(string $piece, $ranks): array
    {
        // This is a vector of (start, rank).
        // The rank is of the byte pair starting at position start.
        // The rank of the last item in the vector is not a valid value.
        $parts = new \Ds\Vector();
        $parts->allocate(strlen($piece) + 1);
        for ($i = 0; $i < strlen($piece) + 1; $i++) {
            $parts[] = [$i, PHP_INT_MAX];
        }

        $getRank = function (&$parts, int $startIdx, int $skip) use (&$ranks, &$piece) {
            if (($startIdx + $skip + 2) < count($parts)) {
                $subStart = $parts[$startIdx][0];
                $subEnd = $parts[$startIdx + $skip + 2][0];

                return $ranks[substr($piece, $subStart, $subEnd - $subStart)] ?? null;
            } else {
                return null;
            }
        };

        // We look up the ranks once in the beginning and iteratively update
        // them during each merge, which reduces the number of rank lookups.
        for ($i = 0; $i < count($parts) - 2; $i++) {
            $rank = $getRank($parts, $i, 0);
            if (!is_null($rank)) {
                assert($rank !== PHP_INT_MAX);
                $parts[$i][1] = $rank;
            }
        }

        // If you have n parts and m merges, this does O(mn) work.
        // We could do something with a heap and do O(m log n) work.
        // It is important to consider that n is often small (<100), and as such
        // the cache-locality benefits outweigh the algorithmic complexity downsides
        // of the `parts` vector data structure above.

        // Note that we hash bytes, not token pairs. As long as we train BPE the way we
        // currently do, this is equivalent. An easy way to break this would be to decouple
        // merge priority from token index or to prevent specific token merges.
        while (true) {
            if (count($parts) == 1) {
                break;
            }

            // PHP_INT_MAX is a sentinel rank value allowing us to
            // take the min more quickly
            $minRank = [PHP_INT_MAX, 0];
            for ($i = 0; $i < count($parts) - 1; $i++) {
                if ($parts[$i][1] < $minRank[0]) {
                    $minRank = [$parts[$i][1], $i];
                }
            }

            if ($minRank[0] !== PHP_INT_MAX) {
                $i = $minRank[1];

                // NOTE: We are about to remove parts[i + 1]. We do not do it
                // yet because there are cache-locality benefits to updating
                // parts[i] and parts[i-1] before removing, which could thrash
                // the cache. Thus, we update the rank calculation by skipping over
                // parts[i + 1], by invoking `get_rank!` with `skip = 1`.
                $newValue = $getRank($parts, $i, 1);
                $parts[$i][1] = is_null($newValue) ? PHP_INT_MAX : $newValue;
                if ($i > 0) {
                    $newValue = $getRank($parts, $i - 1, 1);
                    $parts[$i - 1][1] = is_null($newValue) ? PHP_INT_MAX : $newValue;
                }

                $parts->remove($i + 1);
            } else {
                break;
            }
        }

        $out = [];
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $start = $parts[$i][0];
            $end = $parts[$i + 1][0];

            $out[] = $ranks[substr($piece, $start, $end - $start)];
        }

        return $out;
    }
}
