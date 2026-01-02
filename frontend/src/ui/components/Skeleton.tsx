import React from 'react';

interface SkeletonProps {
  className?: string;
  variant?: 'text' | 'circular' | 'rectangular' | 'rounded';
  width?: string | number;
  height?: string | number;
  animation?: 'pulse' | 'wave' | 'none';
}

// Base skeleton component
export const Skeleton: React.FC<SkeletonProps> = ({
  className = '',
  variant = 'rectangular',
  width,
  height,
  animation = 'pulse',
}) => {
  const variantClasses = {
    text: 'rounded',
    circular: 'rounded-full',
    rectangular: '',
    rounded: 'rounded-lg',
  };

  const animationClasses = {
    pulse: 'animate-pulse',
    wave: 'animate-shimmer',
    none: '',
  };

  const style: React.CSSProperties = {
    width: width ?? '100%',
    height: height ?? (variant === 'text' ? '1em' : undefined),
  };

  return (
    <div
      className={`bg-slate-200 dark:bg-slate-700 ${variantClasses[variant]} ${animationClasses[animation]} ${className}`}
      style={style}
    />
  );
};

// Card skeleton for course/item cards
export const CardSkeleton: React.FC<{ className?: string }> = ({ className = '' }) => (
  <div className={`bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 ${className}`}>
    <Skeleton variant="rounded" height={160} className="mb-4" />
    <Skeleton variant="text" height={24} width="70%" className="mb-2" />
    <Skeleton variant="text" height={16} width="40%" className="mb-4" />
    <div className="flex gap-2">
      <Skeleton variant="rounded" height={24} width={60} />
      <Skeleton variant="rounded" height={24} width={80} />
    </div>
  </div>
);

// List item skeleton
export const ListItemSkeleton: React.FC<{ hasAvatar?: boolean; className?: string }> = ({
  hasAvatar = true,
  className = '',
}) => (
  <div className={`flex items-center gap-4 p-4 bg-white dark:bg-slate-800 rounded-lg ${className}`}>
    {hasAvatar && <Skeleton variant="circular" width={48} height={48} />}
    <div className="flex-1">
      <Skeleton variant="text" height={20} width="60%" className="mb-2" />
      <Skeleton variant="text" height={14} width="40%" />
    </div>
    <Skeleton variant="rounded" height={32} width={80} />
  </div>
);

// Table skeleton
export const TableSkeleton: React.FC<{ rows?: number; columns?: number; className?: string }> = ({
  rows = 5,
  columns = 4,
  className = '',
}) => (
  <div className={`bg-white dark:bg-slate-800 rounded-xl shadow overflow-hidden ${className}`}>
    {/* Header */}
    <div className="flex gap-4 p-4 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
      {Array.from({ length: columns }).map((_, i) => (
        <Skeleton key={i} variant="text" height={16} className="flex-1" />
      ))}
    </div>
    {/* Rows */}
    {Array.from({ length: rows }).map((_, rowIndex) => (
      <div
        key={rowIndex}
        className="flex gap-4 p-4 border-b border-slate-100 dark:border-slate-800 last:border-b-0"
      >
        {Array.from({ length: columns }).map((_, colIndex) => (
          <Skeleton key={colIndex} variant="text" height={14} className="flex-1" />
        ))}
      </div>
    ))}
  </div>
);

// Dashboard stat card skeleton
export const StatCardSkeleton: React.FC<{ className?: string }> = ({ className = '' }) => (
  <div className={`bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 ${className}`}>
    <div className="flex items-center justify-between mb-4">
      <Skeleton variant="circular" width={48} height={48} />
      <Skeleton variant="text" height={12} width={60} />
    </div>
    <Skeleton variant="text" height={32} width="50%" className="mb-2" />
    <Skeleton variant="text" height={14} width="70%" />
  </div>
);

// Form skeleton
export const FormSkeleton: React.FC<{ fields?: number; className?: string }> = ({
  fields = 4,
  className = '',
}) => (
  <div className={`space-y-6 ${className}`}>
    {Array.from({ length: fields }).map((_, i) => (
      <div key={i}>
        <Skeleton variant="text" height={14} width={100} className="mb-2" />
        <Skeleton variant="rounded" height={40} />
      </div>
    ))}
    <div className="flex gap-4 pt-4">
      <Skeleton variant="rounded" height={44} width={120} />
      <Skeleton variant="rounded" height={44} width={100} />
    </div>
  </div>
);

// Course detail skeleton
export const CourseDetailSkeleton: React.FC = () => (
  <div className="space-y-6">
    {/* Header */}
    <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6">
      <Skeleton variant="text" height={36} width="60%" className="mb-4" />
      <div className="flex gap-4 mb-4">
        <Skeleton variant="rounded" height={24} width={80} />
        <Skeleton variant="rounded" height={24} width={100} />
        <Skeleton variant="rounded" height={24} width={60} />
      </div>
      <Skeleton variant="text" height={16} className="mb-2" />
      <Skeleton variant="text" height={16} width="80%" />
    </div>
    {/* Content */}
    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div className="lg:col-span-2 space-y-4">
        {Array.from({ length: 4 }).map((_, i) => (
          <ListItemSkeleton key={i} hasAvatar={false} />
        ))}
      </div>
      <div className="space-y-4">
        <StatCardSkeleton />
        <StatCardSkeleton />
      </div>
    </div>
  </div>
);

// Dashboard skeleton
export const DashboardSkeleton: React.FC = () => (
  <div className="space-y-6">
    {/* Stats row */}
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      {Array.from({ length: 4 }).map((_, i) => (
        <StatCardSkeleton key={i} />
      ))}
    </div>
    {/* Content */}
    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6">
        <Skeleton variant="text" height={24} width="40%" className="mb-4" />
        <Skeleton variant="rounded" height={200} />
      </div>
      <div className="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6">
        <Skeleton variant="text" height={24} width="40%" className="mb-4" />
        <div className="space-y-3">
          {Array.from({ length: 5 }).map((_, i) => (
            <ListItemSkeleton key={i} />
          ))}
        </div>
      </div>
    </div>
  </div>
);

export default Skeleton;
