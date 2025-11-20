PHP_REQUIRE_CXX()
PHP_ADD_LIBRARY(stdc++, , FIFTYONEDEGREESHASHENGINE_LIBADD)

PHP_ARG_ENABLE(FiftyOneDegreesHashEngine, whether to enable 51Degrees Hash engine,
[ --enable-FiftyOneDegreesHashEngine   Enable 51Degrees Hash Device Detection])

CFLAGS="$CFLAGS -std=gnu11 -Wall -Werror -Wno-missing-braces -Wno-unused-variable -Wno-unused-but-set-variable -Wno-unused-function -Wno-strict-aliasing"
CXXFLAGS="${CXXFLAGS} -std=gnu++17 -fpermissive -Wall -Werror -Wno-write-strings -Wno-delete-non-virtual-dtor -Wno-unused-label -Wno-unused-variable"
case "$host_os" in
linux*) LDFLAGS="$LDFLAGS -latomic" ;;
esac
PHP_SUBST([CFLAGS])
PHP_SUBST([CXXFLAGS])
PHP_SUBST([LDFLAGS])

dnl Check PHP version:
AC_MSG_CHECKING(PHP version)
if test ! -z "$PHP_CONFIG"; then
  PHP_VERSION=`$PHP_CONFIG --version 2>/dev/null`
fi
if test x"$PHP_VERSION" = "x"; then
    AC_MSG_WARN([none])
else
    PHP_MAJOR_VERSION=`echo $PHP_VERSION | sed -e 's/\([[0-9]]*\)\.\([[0-9]]*\)\.\([[0-9]]*\).*/\1/g' 2>/dev/null`
    PHP_MINOR_VERSION=`echo $PHP_VERSION | sed -e 's/\([[0-9]]*\)\.\([[0-9]]*\)\.\([[0-9]]*\).*/\2/g' 2>/dev/null`
    PHP_RELEASE_VERSION=`echo $PHP_VERSION | sed -e 's/\([[0-9]]*\)\.\([[0-9]]*\)\.\([[0-9]]*\).*/\3/g' 2>/dev/null`
    AC_MSG_RESULT([$PHP_MAJOR_VERSION].[$PHP_MINOR_VERSION])
fi

if test "$PHP_FIFTYONEDEGREESHASHENGINE" = "yes"; then
  AC_DEFINE(HAVE_FIFTYONEDEGREESHASHENGINE, 1, [Whether you have 51Degrees Hash engine Enabled])
  PHP_SUBST(FIFTYONEDEGREESHASHENGINE_LIBADD)

  AC_CONFIG_COMMANDS_PRE(mkdir -p src/php$PHP_MAJOR_VERSION)

  AC_MSG_CHECKING(SWIG version)
  if test -n "$SWIG"; then
    SWIG_VERSION=`swig -version | grep -Po '(?<=SWIG Version )[[^ ]]+' 2>/dev/null`
    SWIG_MAJOR_VERSION=`echo $SWIG_VERSION | sed -e 's/\([[0-9]]*\)\.\([[0-9]]*\)\.\([[0-9]]*\).*/\1/g' 2>/dev/null`
    SWIG_MINOR_VERSION=`echo $SWIG_VERSION | sed -e 's/\([[0-9]]*\)\.\([[0-9]]*\)\.\([[0-9]]*\).*/\2/g' 2>/dev/null`
    AC_MSG_RESULT([$SWIG_MAJOR_VERSION].[$SWIG_MINOR_VERSION])  

    if [[ "$PHP_MAJOR_VERSION" -ge 8 ]] && ([[ "$SWIG_MAJOR_VERSION" -ne 4 ]] || [[ "$SWIG_MINOR_VERSION" -lt 1 ]]); then
      AC_MSG_ERROR(swig version 4.1.0 or higher is required for PHP 8 and above)
    fi

    if [[ "$PHP_MAJOR_VERSION" -lt 7 ]] && [[ "$SWIG_MAJOR_VERSION" -gt 3 ]]; then
      AC_MSG_ERROR(swig version 3.x.x or lower is required for PHP versions ealier than 7 e.g. PHP 5)
    fi

    if [[ "$SWIG_MAJOR_VERSION" -eq 3 ]]; then
      SWIGHASH="swig -c++ -php$PHP_MAJOR_VERSION -module FiftyOneDegreesHashEngine -outdir src/php$PHP_MAJOR_VERSION -o src/php$PHP_MAJOR_VERSION/hash_wrap.cxx hash-php.i"
    else 
      if [[ "$SWIG_MAJOR_VERSION" -eq 4 ]]; then
        SWIGHASH="swig -c++ -php -module FiftyOneDegreesHashEngine -outdir src/php$PHP_MAJOR_VERSION -o src/php$PHP_MAJOR_VERSION/hash_wrap.cxx hash-php.i"
      else 
        AC_MSG_ERROR(invalid swig version $SWIG_MAJOR_VERSION, swig version 3 and 4 are supported)
      fi
    fi
    
    AC_MSG_NOTICE(genertaing SWIG wrapper)
    AC_CONFIG_COMMANDS_PRE($SWIGHASH)
  fi
  AC_CONFIG_COMMANDS_PRE(cp src/php$PHP_MAJOR_VERSION/* .)

  PHP_NEW_EXTENSION(FiftyOneDegreesHashEngine, hash_wrap.cxx device-detection-cxx/src/*.c device-detection-cxx/src/*.cpp device-detection-cxx/src/common-cxx/*.c device-detection-cxx/src/common-cxx/*.cpp device-detection-cxx/src/hash/*.c device-detection-cxx/src/hash/*.cpp, $ext_shared, ,,"yes")
fi
