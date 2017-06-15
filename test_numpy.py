# numpy예제
import numpy as np
## import numpy.linalg as lin

x = np.array([[1,2,3],[4,5,6]])
y = np.array([[1,0,-1],[1,1,0]])

t = np.dot(x,y.T) # 행렬의 곱셈,,,,

# x의 행렬
print('x의 행렬')
print(x)

# y의 역행렬
print('y의 역행렬')
print(y.T)

# 결과
print('결과')
print(t)

# x의 행렬
# [[1 2 3]
#  [4 5 6]]
# y의 역행렬
# [[ 1  1]
#  [ 0  1]
#  [-1  0]]
# 결과
# [[-2  3]
#  [-2  9]]