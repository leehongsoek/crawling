import sys

# https://pypi.python.org/pypi/cx_Freeze

# python -m pip install cx_Freeze --upgrade


from cx_Freeze import setup, Executable


# setup(  name = "parser",
#         version = "1.0",
#         description = "Parser",
#         author = "sh1n2",
#         executables = [Executable("crawl_mega.py")])

setup(  name = "parser",
        version = "1.0",
        description = "Parser",
        author = "sh1n2",
        executables = [Executable("test_Tkinter.py")])
#
