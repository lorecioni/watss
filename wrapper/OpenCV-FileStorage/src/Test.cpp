//============================================================================
// Name        : Main.cpp
// Author      : Lorenzo Cioni
//============================================================================

#include <boost/python.hpp>
#include <string>

char const* greet()
{
   return "hello, world";
}

BOOST_PYTHON_MODULE(hello_ext)
{
    using namespace boost::python;
    def("greet", greet);
}

