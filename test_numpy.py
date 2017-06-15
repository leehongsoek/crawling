
import numpy as np
## import numpy.linalg as lin

x = np.array([[1,2,3],[4,5,6]])
y = np.array([[1,0,-1],[1,1,0]])

t = np.dot(x,y.T)

print(t)