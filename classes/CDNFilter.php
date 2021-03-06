<?php

/*namespace XROW\CDN;*/
/*
 * Instead of an output filter it would be smart to have a nice template operator later on that directly converts the urls
 * 
 */
class CDNFilter
{
    const DIR_NAME = '[.a-z0-9_-]+';
    const PATH_EXP = '(\/[.a-z0-9_-]+)*';
    const BASENAME_EXP = '[.a-z0-9_-]+';
    const MIN_BRACKETS = 11;
    static function buildRegExp($dirs, $suffixes) {
        
        $dirs = '(' . implode ( '|', $dirs ) . ')';
        $suffixes = '(' . implode ( '|', $suffixes ) . ')';
        // [shu][r][cel] improves performance
        return "/([shu][r][cel])(=['\"]|f=['\"]|(\s)*\((\s)*['\"]?(\s)*)(" . $dirs . self::PATH_EXP . '\/' . self::BASENAME_EXP . ')(\.' . $suffixes . ')/imU';
    }
    static function randomHost($rule) {
        $value = eZINI::instance ( 'xrowcdn.ini' )->variable ( 'Rule-' . $rule, 'Replacement' );
        if (is_array ( $value )) {
            return $value [array_rand ( $value, 1 )];
        } else {
            return $value;
        }
    }
    static function filter($output) {
# speed up string matching by removing whitespace
#        $output = preg_replace('~>\s+<~', '><', $output);
        $ini = eZINI::instance ( 'xrowcdn.ini' );

        if( $ini->hasVariable ( 'Settings', 'ExcludeHostList') ) 
        {
            foreach( eZINI::instance ( 'xrowcdn.ini' )->variable ( 'Settings', 'ExcludeHostList' ) as $host ) 
            { 
                if(strpos( $_SERVER['HTTP_HOST'], $host) !== false )  
                {
                    return $output;
                }
            }
        }
        if ( eZSys::isSSLNow () and $ini->hasVariable ( 'Settings', 'SSL' ) ) {
            return $output;
        }
        eZDebug::createAccumulatorGroup ( 'outputfilter_total', 'Outputfilter Total' );
        
        $patterns = array ();
        $replacements = array ();
        if ($ini->hasVariable ( 'Rules', 'List' )) {
            foreach ( $ini->variable ( 'Rules', 'List' ) as $rule ) {
                $dirs = array ();
                $suffix = array ();
                
                if ($ini->hasSection ( 'Rule-' . $rule )) {
                    if ($ini->hasVariable ( 'Rule-' . $rule, 'Dirs' ) and $ini->hasVariable ( 'Rule-' . $rule, 'Suffixes' ) and $ini->hasVariable ( 'Rule-' . $rule, 'Replacement' )) {
                        $dirs = $ini->variable ( 'Rule-' . $rule, 'Dirs' );
                        $suffix = $ini->variable ( 'Rule-' . $rule, 'Suffixes' );

                            $reg = self::buildRegExp ( $dirs, $suffix );
                            $patterns [] = $reg;
                            $count = 0;
                            str_replace ( '(', '(', $reg, $count );
                            $count -= self::MIN_BRACKETS;
                            $functions [] = 'return $matches[1].$matches[2].xrowCDNFilter::randomHost(  "' . $rule . '" ) . $matches[6].$matches[' . (9 + $count) . '];';
                    }
                }
            } // FOREACH
        } // IF ends
        

        eZDebug::accumulatorStart ( 'outputfilter', 'outputfilter_total', 'Output Filtering' );
        //$output = preg_replace($patterns, $replacements, $output );
        foreach ( $patterns as $key => $pattern ) {
            $output = preg_replace_callback ( $pattern, create_function ( '$matches', $functions [$key] ), $output );
        }
        eZDebug::accumulatorStop ( 'outputfilter' );
        
        return $output;
    }
}