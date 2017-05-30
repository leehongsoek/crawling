import os
import sys
from cx_Freeze import setup, Executable
import os.path # http://stackoverflow.com/questions/35533803/keyerror-tcl-library-when-i-use-cx-freeze

PYTHON_INSTALL_DIR = os.path.dirname(os.path.dirname(os.__file__))

os.environ['TCL_LIBRARY'] = os.path.join(PYTHON_INSTALL_DIR, 'tcl', 'tcl8.6')
os.environ['TK_LIBRARY'] = os.path.join(PYTHON_INSTALL_DIR, 'tcl', 'tk8.6')

# os.environ['TCL_LIBRARY'] = "C:\\Users\\lgleehs\\Anaconda3\\tcl\\tcl8.6"
# os.environ['TK_LIBRARY'] = "C:\\Users\\lgleehs\\Anaconda3\\tcl\\tcl8.6"

# Dependencies are automatically detected, but it might need fine tuning.
build_exe_options = {"includes": ["tkinter"],
                     'include_files': [
                         os.path.join( PYTHON_INSTALL_DIR, 'DLLs', 'tk86t.dll' ),
                         os.path.join( PYTHON_INSTALL_DIR, 'DLLs', 'tcl86t.dll' ),
                     ],
                    }

# GUI applications require a different base on Windows (the default is for a
# console application).
base = None
if sys.platform == "win32":
    base = "Win32GUI"

setup(
    name = "simple_Tkinter",
    version = "0.1",
    description = "Sample cx_Freeze Tkinter script",
    options = {"build_exe": build_exe_options},
    executables = [Executable("test_Tkinter.py", base = base)])
#
