#ifndef _MOBOTCOMMS_H_
#define _MOBOTCOMMS_H_

#ifdef SWIG
#define DLLIMPORT
%module mobot
%feature("autodoc", "1");
%{
#include "mobot.h"
%}
#endif

#include <math.h>

#ifdef _CH_
#pragma package <chmobot>
#ifdef _WIN32_
#define _WIN32
#include <stdint.h>
#define UINT8 uint8_t
#endif
#endif

#include <stdio.h>

#ifdef NONRELEASE
#ifndef _CH_
#ifndef _WIN32
#include <unistd.h>
#include <sys/types.h>
#include <sys/socket.h>
#ifndef __MACH__
#include <bluetooth/bluetooth.h>
#include <bluetooth/rfcomm.h>
#endif
#else
//#include <types.h>
#include <winsock2.h>
#endif
#endif /* Not if CH */

#ifndef M_PI
#define M_PI 3.14159265358979323846
#endif

#ifdef _WIN32
#include <Ws2bth.h>
#endif
#ifndef _CH_
#include "thread_macros.h"
#endif
#else // Release
#define DLLIMPORT
#define THREAD_T void
#define MUTEX_T void
#define COND_T void
#define sockaddr_t void
#ifdef _WIN32
#ifndef _CH_
typedef unsigned char uint8_t;
#endif
#endif
#endif

#ifdef _MSYS
#undef _WIN32
#endif

#ifndef _WIN32
#ifndef _llvm_
#include <stdint.h>
#else
typedef unsigned char uint8_t;
#endif // _llvm_
#ifdef NONRELEASE
typedef struct sockaddr_rc sockaddr_t;
#endif
#else
#ifndef _CH_
typedef unsigned char uint8_t;
typedef unsigned __int32 uint32_t;
#endif
#define AF_BLUETOOTH AF_BTH
#define BTPROTO_RFCOMM BTHPROTO_RFCOMM
#ifndef _CH_
typedef SOCKADDR_BTH sockaddr_t;
#endif
#endif

typedef double* mobotRecordData_t;
#define angle2distance(radius, angle) ((radius) * ((angle) * M_PI / 180.0))
#define distance2angle(radius, distance) (((distance) / (radius)) * 180 / M_PI)

typedef enum mobotConnectionMode_e
{
  MOBOTCONNECT_NONE,
  MOBOTCONNECT_BLUETOOTH,
  MOBOTCONNECT_TCP,
  MOBOTCONNECT_TTY,
  MOBOTCONNECT_NUMMODES
} mobotConnectionMode_t;


#ifndef BR_COMMS_S
#define BR_COMMS_S
typedef struct mobot_s
{
  int socket;
  int connected;
  int connectionMode;
#ifndef __MACH__
  sockaddr_t *addr;
#endif
  double jointSpeeds[4];
  double maxSpeed[4];
  THREAD_T* thread;
  MUTEX_T* commsLock;
  int motionInProgress;
  MUTEX_T* recordingLock;
  int recordingInProgress[4];
  int recordingNumValues[4];

  THREAD_T* commsThread;
  uint8_t recvBuf[64];
  int recvBuf_ready;
  MUTEX_T* recvBuf_lock;
  COND_T*  recvBuf_cond;
  int recvBuf_bytes;
  int commsBusy;
  MUTEX_T* commsBusy_lock;
  COND_T* commsBusy_cond;

  MUTEX_T* callback_lock;
  int callbackEnabled;
  void (*buttonCallback)(void* mobot, int button, int buttonDown);
  void* mobot;
  char* configFilePath;
  void* itemsToFreeOnExit[64];
  int numItemsToFreeOnExit;
} mobot_t;
#endif

#ifndef MOTION_ARG_S
#define MOTION_ARG_S
typedef struct motionArg_s
{
  int i;
  double d;
  mobot_t* mobot;
}motionArg_t;
#endif

#ifdef NONRELEASE
#ifndef _CH_
#include "mobot_internal.h"
#endif
#endif

extern int g_numConnected;

#ifndef MOBOT_JOINTS_E
#define MOBOT_JOINTS_E
typedef enum mobotJoints_e {
  MOBOT_ZERO,
  MOBOT_JOINT1,
  MOBOT_JOINT2,
  MOBOT_JOINT3,
  MOBOT_JOINT4,
  ROBOT_NUM_JOINTS = 4
} mobotJointId_t;
#endif

#ifndef MOBOT_JOINT_STATE_E
#define MOBOT_JOINT_STATE_E
typedef enum mobotJointState_e
{
    MOBOT_NEUTRAL = 0,
    MOBOT_FORWARD,
    MOBOT_BACKWARD,
    MOBOT_HOLD,
} mobotJointState_t;
#endif

#ifndef _CH_

#ifdef __cplusplus
extern "C" {
#endif
  DLLIMPORT double deg2rad(double deg);
  DLLIMPORT double rad2deg(double rad);
#ifndef SWIG
  DLLIMPORT void delay(double seconds);
#endif
#ifdef __cplusplus
}
#endif

typedef struct recordAngleArg_s 
{
  mobot_t* comms;
  mobotJointId_t id;
  double *time;
  double *angle;
  double *angle2;
  double *angle3;
  double *angle4;
  double **time_p;
  double **angle_p;
  double **angle2_p;
  double **angle3_p;
  double **angle4_p;
  int num;
  int i; // Number of recorded items
  int msecs;
} recordAngleArg_t;
#endif

#ifndef C_ONLY
#if defined (__cplusplus) || defined (_CH_) || defined (SWIG)
class CMobot 
{
  public:
    CMobot();
    ~CMobot();
    int blinkLED(double delay, int numBlinks);
/* connect() Return Error Codes:
   -1 : General Error
   -2 : Lockfile Exists
   -3 : Address Format Incorrect
   -4 : Not enough entries in the configuration file
   -5 : Bluetooth device not found
   -6 : Protocol version mismatch
   */
    int connect();
    int connectWithAddress(const char address[], int channel);
#ifndef _WIN32
    int connectWithTTY(const char ttyfilename[]);
#endif
    int disconnect();
    int driveJointToDirect(mobotJointId_t id, double angle);
    int driveJointToDirectNB(mobotJointId_t id, double angle);
    int driveToDirect(double angle1, double angle2, double angle3, double angle4);
    int driveToDirectNB(double angle1, double angle2, double angle3, double angle4);
    int enableButtonCallback(void (*buttonCallback)(CMobot* mobot, int button, int buttonDown));
    int disableButtonCallback();
    int isConnected();
    int isMoving();
#ifdef SWIG
    %apply double & OUTPUT {double &angle};
    %apply double & OUTPUT {double &angle1, double &angle2, double &angle3, double &angle4};
    %apply double & OUTPUT {double &maxSpeed};
    %apply double & OUTPUT {double &seconds};
    %apply double & OUTPUT {double &speed};
    %apply double & OUTPUT {double &ratio};
    %apply double & OUTPUT {double &speed1, double &speed2, double &speed3, double &speed4};
    %apply double & OUTPUT {double &ratio1, double &ratio2, double &ratio3, double &ratio4};
    %apply double & OUTPUT {mobotJointState_t &state};
#endif
    static const char* getConfigFilePath();
    int getJointAngle(mobotJointId_t id, double &angle);
    int getJointAngles(double &angle1, double &angle2, double &angle3, double &angle4);
    int getJointMaxSpeed(mobotJointId_t id, double &maxSpeed);
    int getJointSafetyAngle(double &angle);
    int getJointSafetyAngleTimeout(double &seconds);
    int getJointSpeed(mobotJointId_t id, double &speed);
    int getJointSpeedRatio(mobotJointId_t id, double &ratio);
    int getJointSpeeds(double &speed1, double &speed2, double &speed3, double &speed4);
    int getJointSpeedRatios(double &ratio1, double &ratio2, double &ratio3, double &ratio4);
    int getJointState(mobotJointId_t id, mobotJointState_t &state);
    mobot_t* getMobotObject();
    int move(double angle1, double angle2, double angle3, double angle4);
    int moveNB(double angle1, double angle2, double angle3, double angle4);
    int moveContinuousNB(mobotJointState_t dir1, 
                       mobotJointState_t dir2, 
                       mobotJointState_t dir3, 
                       mobotJointState_t dir4);
    int moveContinuousTime(mobotJointState_t dir1, 
                           mobotJointState_t dir2, 
                           mobotJointState_t dir3, 
                           mobotJointState_t dir4, 
                           double seconds);
    int moveJointContinuousNB(mobotJointId_t id, mobotJointState_t dir);
    int moveJointContinuousTime(mobotJointId_t id, mobotJointState_t dir, double seconds);
    int moveJoint(mobotJointId_t id, double angle);
    int moveJointNB(mobotJointId_t id, double angle);
    int moveJointTo(mobotJointId_t id, double angle);
    int moveJointToDirect(mobotJointId_t id, double angle);
    int moveJointToNB(mobotJointId_t id, double angle);
    int moveJointToDirectNB(mobotJointId_t id, double angle);
    int moveJointWait(mobotJointId_t id);
    int moveTo(double angle1, double angle2, double angle3, double angle4);
    int moveToDirect(double angle1, double angle2, double angle3, double angle4);
    int moveToNB(double angle1, double angle2, double angle3, double angle4);
    int moveToDirectNB(double angle1, double angle2, double angle3, double angle4);
    int moveWait();
    int moveToZero();
    int moveToZeroNB();
#ifdef _CH_
    int recordAngle(mobotJointId_t id, double time[:], double angle[:], int num, double seconds);
    int recordAngles(double time[:], 
                     double angle1[:], 
                     double angle2[:], 
                     double angle3[:], 
                     double angle4[:], 
                     int num, 
                     double seconds);
#else
    int recordAngle(mobotJointId_t id, double time[], double angle[], int num, double seconds);
    int recordAngles(double time[], 
                     double angle1[], 
                     double angle2[], 
                     double angle3[], 
                     double angle4[], 
                     int num, 
                     double seconds);
#endif
    int recordAngleBegin(mobotJointId_t id, mobotRecordData_t &time, mobotRecordData_t &angle, double seconds);
    int recordAngleEnd(mobotJointId_t id, int &num);
    int recordAnglesBegin(mobotRecordData_t &time, 
                          mobotRecordData_t &angle1, 
                          mobotRecordData_t &angle2, 
                          mobotRecordData_t &angle3, 
                          mobotRecordData_t &angle4, 
                          double seconds);
    int recordAnglesEnd(int &num);
    int recordWait();
    int reset();
    int resetToZero();
    int resetToZeroNB();
    int setJointSafetyAngle(double angle);
    int setJointSafetyAngleTimeout(double seconds);
    int setJointSpeed(mobotJointId_t id, double speed);
    int setJointSpeeds(double speed1, double speed2, double speed3, double speed4);
    int setJointSpeedRatio(mobotJointId_t id, double ratio);
    int setJointSpeedRatios(double ratios1, double ratios2, double ratios3, double ratios4);
    int setMotorPower(mobotJointId_t id, int power);
    int setTwoWheelRobotSpeed(double speed, double radius);
    int stop();
    int stopOneJoint(mobotJointId_t id);
    int stopTwoJoints(mobotJointId_t id1, mobotJointId_t id2);
    int stopThreeJoints(mobotJointId_t id1, mobotJointId_t id2, mobotJointId_t id3);
    int stopAllJoints();

    int motionArch(double angle);
    int motionInchwormLeft(int num);
    int motionInchwormRight(int num);
    int motionRollBackward(double angle);
    int motionRollForward(double angle);
    int motionSkinny(double angle);
    int motionStand();
    int motionTurnLeft(double angle);
    int motionTurnRight(double angle);
    int motionTumbleRight(int num);
    int motionTumbleLeft(int num);
    int motionUnstand();

    /* Non-Blocking motion functions */
    int motionArchNB(double angle);
    int motionInchwormLeftNB(int num);
    int motionInchwormRightNB(int num);
    int motionRollBackwardNB(double angle);
    int motionRollForwardNB(double angle);
    int motionSkinnyNB(double angle);
    int motionStandNB();
    int motionTurnLeftNB(double angle);
    int motionTurnRightNB(double angle);
    int motionTumbleRightNB(int num);
    int motionTumbleLeftNB(int num);
    int motionUnstandNB();
    int motionWait();
#ifndef _CH_
  private:
    int getJointDirection(mobotJointId_t id, mobotJointState_t &dir);
    int setJointDirection(mobotJointId_t id, mobotJointState_t dir);
    mobot_t *_comms;
    void (*buttonCallback)(CMobot *mobot, int button, int buttonDown);
#else
  public:
    static void *g_chmobot_dlhandle;
    static int g_chmobot_dlcount;
#endif /* Not _CH_*/
};

class CMobotGroup
{
  public:
    CMobotGroup();
    ~CMobotGroup();
    int addRobot(CMobot& mobot);
    int driveJointToDirect(mobotJointId_t id, double angle);
    int driveJointToDirectNB(mobotJointId_t id, double angle);
    int driveToDirect(double angle1, double angle2, double angle3, double angle4);
    int driveToDirectNB(double angle1, double angle2, double angle3, double angle4);
    int isMoving();
    int move(double angle1, double angle2, double angle3, double angle4);
    int moveNB(double angle1, double angle2, double angle3, double angle4);
    int moveContinuousNB(mobotJointState_t dir1, 
                       mobotJointState_t dir2, 
                       mobotJointState_t dir3, 
                       mobotJointState_t dir4);
    int moveContinuousTime(mobotJointState_t dir1, 
                           mobotJointState_t dir2, 
                           mobotJointState_t dir3, 
                           mobotJointState_t dir4, 
                           double seconds);
    int moveJointContinuousNB(mobotJointId_t id, mobotJointState_t dir);
    int moveJointContinuousTime(mobotJointId_t id, mobotJointState_t dir, double seconds);
    int moveJointTo(mobotJointId_t id, double angle);
    int moveJointToDirect(mobotJointId_t id, double angle);
    int moveJointToNB(mobotJointId_t id, double angle);
    int moveJointToDirectNB(mobotJointId_t id, double angle);
    int moveJointWait(mobotJointId_t id);
    int moveTo(double angle1, double angle2, double angle3, double angle4);
    int moveToDirect(double angle1, double angle2, double angle3, double angle4);
    int moveToNB(double angle1, double angle2, double angle3, double angle4);
    int moveToDirectNB(double angle1, double angle2, double angle3, double angle4);
    int moveWait();
    int moveToZero();
    int moveToZeroNB();
    int reset();
    int resetToZero();
    int resetToZeroNB();
    int setJointSpeed(mobotJointId_t id, double speed);
    int setJointSpeeds(double speed1, double speed2, double speed3, double speed4);
    int setJointSpeedRatio(mobotJointId_t id, double ratio);
    int setJointSpeedRatios(double ratio1, double ratio2, double ratio3, double ratio4);
    int setTwoWheelRobotSpeed(double speed, double radius);
    int stopAllJoints();
    int stopOneJoint(mobotJointId_t id);
    int stopTwoJoints(mobotJointId_t id1, mobotJointId_t id2);
    int stopThreeJoints(mobotJointId_t id1, mobotJointId_t id2, mobotJointId_t id3);

    int motionArch(double angle);
    int motionArchNB(double angle);
    static void* motionArchThread(void*);
    int motionInchwormLeft(int num);
    int motionInchwormLeftNB(int num);
    static void* motionInchwormLeftThread(void*);
    int motionInchwormRight(int num);
    int motionInchwormRightNB(int num);
    static void* motionInchwormRightThread(void*);
    int motionRollBackward(double angle);
    int motionRollBackwardNB(double angle);
    static void* motionRollBackwardThread(void*);
    int motionRollForward(double angle);
    int motionRollForwardNB(double angle);
    static void* motionRollForwardThread(void*);
    int motionSkinny(double angle);
    int motionSkinnyNB(double angle);
    static void* motionSkinnyThread(void*);
    int motionStand();
    int motionStandNB();
    static void* motionStandThread(void*);
    int motionTurnLeft(double angle);
    int motionTurnLeftNB(double angle);
    static void* motionTurnLeftThread(void*);
    int motionTurnRight(double angle);
    int motionTurnRightNB(double angle);
    static void* motionTurnRightThread(void*);
    int motionTumbleRight(int num);
    int motionTumbleRightNB(int num);
    static void* motionTumbleRightThread(void*);
    int motionTumbleLeft(int num);
    int motionTumbleLeftNB(int num);
    static void* motionTumbleLeftThread(void*);
    int motionUnstand();
    int motionUnstandNB();
    static void* motionUnstandThread(void*);
    int motionWait();

  private:
    int _numRobots;
    CMobot *_robots[64];
    int argInt;
    double argDouble;
#ifndef _CH_
    THREAD_T* _thread;
#else
    void* _thread;
#endif
    int _motionInProgress;
};

#endif /* If C++ or CH */
#endif /* C_ONLY */

#ifdef __cplusplus
extern "C" {
#endif
DLLIMPORT int Mobot_blinkLED(mobot_t* comms, double delay, int numBlinks);
DLLIMPORT int Mobot_connect(mobot_t* comms);
DLLIMPORT int Mobot_connectWithTCP(mobot_t* comms);
#ifndef _WIN32
DLLIMPORT int Mobot_connectWithAddressTTY(mobot_t* comms, const char* address);
DLLIMPORT int Mobot_connectWithTTY(mobot_t* comms, const char* ttyfilename);
#endif
DLLIMPORT int Mobot_connectWithAddress(
    mobot_t* comms, const char* address, int channel);
DLLIMPORT int Mobot_disconnect(mobot_t* comms);
DLLIMPORT int Mobot_driveJointToDirect(mobot_t* comms, mobotJointId_t id, double angle);
DLLIMPORT int Mobot_driveJointToDirectNB(mobot_t* comms, mobotJointId_t id, double angle);
DLLIMPORT int Mobot_driveToDirect(mobot_t* comms,
                               double angle1,
                               double angle2,
                               double angle3,
                               double angle4);
DLLIMPORT int Mobot_driveToDirectNB(mobot_t* comms,
                               double angle1,
                               double angle2,
                               double angle3,
                               double angle4);
DLLIMPORT int Mobot_enableButtonCallback(mobot_t* comms, void* data, void (*buttonCallback)(void* mobot, int button, int buttonDown));
DLLIMPORT int Mobot_disableButtonCallback(mobot_t* comms);
DLLIMPORT int Mobot_init(mobot_t* comms);
DLLIMPORT int Mobot_isConnected(mobot_t* comms);
DLLIMPORT int Mobot_isMoving(mobot_t* comms);
DLLIMPORT int Mobot_getButtonVoltage(mobot_t* comms, double *voltage);
DLLIMPORT const char* Mobot_getConfigFilePath();
DLLIMPORT int Mobot_getEncoderVoltage(mobot_t* comms, int pinNumber, double *voltage);
DLLIMPORT int Mobot_getJointAngle(mobot_t* comms, mobotJointId_t id, double *angle);
DLLIMPORT int Mobot_getJointAnglesTime(mobot_t* comms, 
                                       double *time, 
                                       double *angle1, 
                                       double *angle2, 
                                       double *angle3, 
                                       double *angle4);
DLLIMPORT int Mobot_getJointAngles(mobot_t* comms, 
                                       double *angle1, 
                                       double *angle2, 
                                       double *angle3, 
                                       double *angle4);
DLLIMPORT int Mobot_getJointDirection(mobot_t* comms, mobotJointId_t id, mobotJointState_t *dir);
DLLIMPORT int Mobot_getJointMaxSpeed(mobot_t* comms, mobotJointId_t, double *maxSpeed);
DLLIMPORT int Mobot_getJointSafetyAngle(mobot_t* comms, double *angle);
DLLIMPORT int Mobot_getJointSafetyAngleTimeout(mobot_t* comms, double *seconds);
DLLIMPORT int Mobot_getJointSpeed(mobot_t* comms, mobotJointId_t id, double *speed);
DLLIMPORT int Mobot_getJointSpeedRatio(mobot_t* comms, mobotJointId_t id, double *ratio);
DLLIMPORT int Mobot_getJointSpeedRatios(mobot_t* comms, 
                                        double *ratio1, 
                                        double *ratio2, 
                                        double *ratio3, 
                                        double *ratio4);
DLLIMPORT int Mobot_getJointState(mobot_t* comms, mobotJointId_t id, mobotJointState_t *state);
DLLIMPORT int Mobot_getStatus(mobot_t* comms);
DLLIMPORT int Mobot_getVersion(mobot_t* comms);
DLLIMPORT int Mobot_move(mobot_t* comms,
                               double angle1,
                               double angle2,
                               double angle3,
                               double angle4);
DLLIMPORT int Mobot_moveNB(mobot_t* comms,
                               double angle1,
                               double angle2,
                               double angle3,
                               double angle4);
DLLIMPORT int Mobot_moveContinuousNB(mobot_t* comms,
                                  mobotJointState_t dir1,
                                  mobotJointState_t dir2,
                                  mobotJointState_t dir3,
                                  mobotJointState_t dir4);
DLLIMPORT int Mobot_moveContinuousTime(mobot_t* comms,
                                  mobotJointState_t dir1,
                                  mobotJointState_t dir2,
                                  mobotJointState_t dir3,
                                  mobotJointState_t dir4,
                                  double seconds);
DLLIMPORT int Mobot_moveJoint(mobot_t* comms, mobotJointId_t id, double angle);
DLLIMPORT int Mobot_moveJointNB(mobot_t* comms, mobotJointId_t id, double angle);
DLLIMPORT int Mobot_moveJointContinuousNB(mobot_t* comms, mobotJointId_t id, mobotJointState_t dir);
DLLIMPORT int Mobot_moveJointContinuousTime(mobot_t* comms, 
                                            mobotJointId_t id, 
                                            mobotJointState_t dir, 
                                            double seconds);
DLLIMPORT int Mobot_moveJointTo(mobot_t* comms, mobotJointId_t id, double angle);
DLLIMPORT int Mobot_moveJointToDirect(mobot_t* comms, mobotJointId_t id, double angle);
DLLIMPORT int Mobot_moveJointToNB(mobot_t* comms, mobotJointId_t id, double angle);
DLLIMPORT int Mobot_moveJointToDirectNB(mobot_t* comms, mobotJointId_t id, double angle);
DLLIMPORT int Mobot_moveJointWait(mobot_t* comms, mobotJointId_t id);
DLLIMPORT int Mobot_moveTo(mobot_t* comms,
                               double angle1,
                               double angle2,
                               double angle3,
                               double angle4);
DLLIMPORT int Mobot_moveToDirect(mobot_t* comms,
                               double angle1,
                               double angle2,
                               double angle3,
                               double angle4);
DLLIMPORT int Mobot_moveToNB(mobot_t* comms,
                               double angle1,
                               double angle2,
                               double angle3,
                               double angle4);
DLLIMPORT int Mobot_moveToDirectNB(mobot_t* comms,
                               double angle1,
                               double angle2,
                               double angle3,
                               double angle4);
DLLIMPORT int Mobot_moveToZero(mobot_t* comms);
DLLIMPORT int Mobot_moveToZeroNB(mobot_t* comms);
DLLIMPORT int Mobot_moveWait(mobot_t* comms);
DLLIMPORT int Mobot_recordAngle(mobot_t* comms, 
                                mobotJointId_t id, 
                                double* time, 
                                double* angle, 
                                int num, 
                                double timeInterval);
DLLIMPORT int Mobot_recordAngleBegin(mobot_t* comms,
                                     mobotJointId_t id,
                                     double **time,
                                     double **angle,
                                     double timeInterval);
DLLIMPORT int Mobot_recordAngleEnd(mobot_t* comms, mobotJointId_t id, int *num);
DLLIMPORT int Mobot_recordAngles(mobot_t* comms, 
                                 double *time, 
                                 double* angle1, 
                                 double* angle2,
                                 double* angle3,
                                 double* angle4,
                                 int num,
                                 double timeInterval);
DLLIMPORT int Mobot_recordAnglesBegin(mobot_t* comms,
                                     double **time,
                                     double **angle1,
                                     double **angle2,
                                     double **angle3,
                                     double **angle4,
                                     double timeInterval);
DLLIMPORT int Mobot_recordAnglesEnd(mobot_t* comms, int* num);
DLLIMPORT int Mobot_recordWait(mobot_t* comms);
DLLIMPORT int Mobot_reset(mobot_t* comms);
DLLIMPORT int Mobot_resetToZero(mobot_t* comms);
DLLIMPORT int Mobot_resetToZeroNB(mobot_t* comms);
DLLIMPORT int Mobot_setJointDirection(mobot_t* comms, mobotJointId_t id, mobotJointState_t dir);
DLLIMPORT int Mobot_setJointSafetyAngle(mobot_t* comms, double angle);
DLLIMPORT int Mobot_setJointSafetyAngleTimeout(mobot_t* comms, double seconds);
DLLIMPORT int Mobot_setJointSpeed(mobot_t* comms, mobotJointId_t id, double speed);
DLLIMPORT int Mobot_setJointSpeedRatio(mobot_t* comms, mobotJointId_t id, double ratio);
DLLIMPORT int Mobot_setJointSpeeds(mobot_t* comms, 
                                   double speeds1, 
                                   double speeds2, 
                                   double speeds3, 
                                   double speeds4);
DLLIMPORT int Mobot_setJointSpeedRatios(mobot_t* comms, 
                                        double ratio1, 
                                        double ratio2, 
                                        double ratio3, 
                                        double ratio4);
DLLIMPORT int Mobot_getJointSpeeds(mobot_t* comms, 
                                   double *speed1, 
                                   double *speed2, 
                                   double *speed3, 
                                   double *speed4);
DLLIMPORT int Mobot_setMotorPower(mobot_t* comms, mobotJointId_t id, int power);
DLLIMPORT int Mobot_setTwoWheelRobotSpeed(mobot_t* comms, double speed, double radius);
DLLIMPORT int Mobot_stop(mobot_t* comms);
DLLIMPORT int Mobot_stopOneJoint(mobot_t* comms, mobotJointId_t id);
DLLIMPORT int Mobot_stopTwoJoints(mobot_t* comms, mobotJointId_t id1, mobotJointId_t id2);
DLLIMPORT int Mobot_stopThreeJoints(mobot_t* comms, mobotJointId_t id1, mobotJointId_t id2, mobotJointId_t id3);
DLLIMPORT int Mobot_stopAllJoints(mobot_t* comms);

/* compound motion functions */
DLLIMPORT int Mobot_motionArch(mobot_t* comms, double angle);
DLLIMPORT int Mobot_motionInchwormLeft(mobot_t* comms, int num);
DLLIMPORT int Mobot_motionInchwormRight(mobot_t* comms, int num);
DLLIMPORT int Mobot_motionRollBackward(mobot_t* comms, double angle);
DLLIMPORT int Mobot_motionRollForward(mobot_t* comms, double angle);
DLLIMPORT int Mobot_motionSkinny(mobot_t* comms, double angle);
DLLIMPORT int Mobot_motionStand(mobot_t* comms);
DLLIMPORT int Mobot_motionTumbleRight(mobot_t* comms, int num);
DLLIMPORT int Mobot_motionTumbleLeft(mobot_t* comms, int num);
DLLIMPORT int Mobot_motionTurnLeft(mobot_t* comms, double angle);
DLLIMPORT int Mobot_motionTurnRight(mobot_t* comms, double angle);
DLLIMPORT int Mobot_motionUnstand(mobot_t* comms);
DLLIMPORT int SendToIMobot(mobot_t* comms, uint8_t cmd, const void* data, int datasize);
DLLIMPORT int SendToMobotDirect(mobot_t* comms, const void* data, int datasize);
DLLIMPORT int RecvFromIMobot(mobot_t* comms, uint8_t* buf, int size);

/* Non-Blocking compound motion functions */
DLLIMPORT int Mobot_motionArchNB(mobot_t* comms, double angle);
DLLIMPORT int Mobot_motionInchwormLeftNB(mobot_t* comms, int num);
DLLIMPORT int Mobot_motionInchwormRightNB(mobot_t* comms, int num);
DLLIMPORT int Mobot_motionRollBackwardNB(mobot_t* comms, double angle);
DLLIMPORT int Mobot_motionRollForwardNB(mobot_t* comms, double angle);
DLLIMPORT int Mobot_motionSkinnyNB(mobot_t* comms, double angle);
DLLIMPORT int Mobot_motionStandNB(mobot_t* comms);
DLLIMPORT int Mobot_motionTumbleRightNB(mobot_t* comms, int num);
DLLIMPORT int Mobot_motionTumbleLeftNB(mobot_t* comms, int num);
DLLIMPORT int Mobot_motionTurnLeftNB(mobot_t* comms, double angle);
DLLIMPORT int Mobot_motionTurnRightNB(mobot_t* comms, double angle);
DLLIMPORT int Mobot_motionUnstandNB(mobot_t* comms);
DLLIMPORT int Mobot_motionWait(mobot_t* comms);
#ifdef __cplusplus
}
#endif

#ifdef _CH_
extern void delay(double seconds);
#endif

#ifdef _CH_
void * CMobot::g_chmobot_dlhandle = NULL;
int CMobot::g_chmobot_dlcount = 0;
#pragma importf "chmobot.chf"
#endif

#endif /* Header Guard */
