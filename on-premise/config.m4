PHP_REQUIRE_CXX()
PHP_ADD_LIBRARY(stdc++, , FIFTYONEDEGREESHASHENGINE_LIBADD)

PHP_ARG_ENABLE(FiftyOneDegreesHashEngine, whether to enable 51Degrees Hash engine,
[ --enable-FiftyOneDegreesHashEngine   Enable 51Degrees Hash Device Detection])

CFLAGS="$CFLAGS -std=gnu11 -Wall -Werror -Wno-missing-braces -Wno-unused-variable -Wno-strict-aliasing"
CXXFLAGS="${CXXFLAGS} -std=gnu++11 -fpermissive -Wall -Werror -Wno-write-strings -Wno-delete-non-virtual-dtor -Wno-unused-label"
LDFLAGS="$LDFLAGS -lrt -latomic"
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

  if test -n "$SWIG"; then
    SWIGHASH="swig -c++ -php$PHP_MAJOR_VERSION -module FiftyOneDegreesHashEngine -outdir src/php$PHP_MAJOR_VERSION -o src/php$PHP_MAJOR_VERSION/hash_wrap.cxx hash-php.i"
    AC_CONFIG_COMMANDS_PRE($SWIGHASH)
  fi
  AC_CONFIG_COMMANDS_PRE(cp src/php$PHP_MAJOR_VERSION/* .)

  PHP_NEW_EXTENSION(FiftyOneDegreesHashEngine, hash_wrap.cxx device-detection-cxx/src/*.c device-detection-cxx/src/*.cpp device-detection-cxx/src/common-cxx/*.c device-detection-cxx/src/common-cxx/*.cpp device-detection-cxx/src/hash/*.c device-detection-cxx/src/hash/*.cpp, $ext_shared, ,,"yes")
fi
