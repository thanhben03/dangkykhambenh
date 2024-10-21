<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Contracts\LocalizedEnum;
use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Specialty extends Enum implements LocalizedEnum
{
    const XET_NGHIEM = 'Xét Nghiệm';
    const NHI_KHOA = 'Nhi khoa';
    const SAN_PHU_KHOA = 'Sản Phụ khoa';
    const CO_XUONG_KHOP = 'Cơ - xương - khớp';
    const KHOA_NGOAI = 'Khoa Ngoại';
    const TAI_MUI_HONG = 'Tai Mũi Họng';
    const TIM_MACH = 'Tim mạch';
    const TIEU_HOA_GAN_MAT = 'Tiêu Hoa Gan Mật';
    const NOI_TONG_QUAT = 'Nội tổng quát';
    const MAT = 'Mắt';
    const RANG_HAM_MAT = 'Răng - Hàm - Mặt';
    const NOI_THAN_KINH = 'Nội Thần Kinh';
    const TAM_THE = 'Tâm Thể';
    const NOI_TIET = 'Nội Tiết';
    const DI_UNG_MIEN_DICH = 'Dị ứng miễn dịch';
    const HO_HAP = 'Hô Hấp';
    const TU_VAN_GIAC_NGU = 'Tư vấn giấc ngủ';
    const KSK_TONG_QUAT = 'Khám sức khỏe tổng quát';
    const KSK_HAU_COVID = 'Khám sức khỏe hậu Covid-19';
    const DA_LIEU = 'Da Liễu';
}
